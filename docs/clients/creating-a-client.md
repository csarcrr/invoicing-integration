# Creating a Client

Register clients in your invoicing provider using `Client::create()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

$clientData = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => '509876543',
    'email' => 'invoices@techstore.pt',
]);

$client = Client::create($clientData)->execute()->getClient();

// Store the provider ID for future orders
$providerClientId = $client->id; // e.g., 12345
```

Example response (`$client->toArray()`):

```json
{
    "id": 12345,
    "name": "TechStore Portugal Lda",
    "vat": "509876543",
    "email": "invoices@techstore.pt"
}
```

## ClientData Properties

Build complete customer profiles with all available fields:

```php
$clientData = ClientData::make([
    // Business identity
    'name' => 'Distribuidor Norte Lda',
    'vat' => '509999888',

    // Contact information
    'email' => 'finance@distrinorte.pt',
    'phone' => '220123456',

    // Billing address
    'address' => 'Zona Industrial de Maia, Lote 42',
    'city' => 'Maia',
    'postalCode' => '4470-000',
    'country' => 'PT',

    // Billing settings
    'defaultPayDue' => DueDateTerm::Days30, // NET30 payment terms
    'emailNotification' => true,    // Auto-send invoices via email
    'irsRetention' => false,        // IRS withholding (for services)

    // Internal notes (not shown on invoices)
    'notes' => 'Volume customer - approved for NET30 by Finance Dept',
]);
```

### Available Fields

| Field               | Description                            |
| ------------------- | -------------------------------------- |
| `name`              | Company or individual name             |
| `vat`               | Tax ID / VAT number                    |
| `email`             | Billing email (validated format)       |
| `phone`             | Phone number                           |
| `address`           | Street address                         |
| `city`              | City name                              |
| `postalCode`        | Postal/ZIP code                        |
| `country`           | ISO 3166-1 alpha-2 code (e.g., PT, ES) |
| `language`          | Language code (e.g., `['code' => 'pt']`) |
| `notes`             | Internal notes (not on invoices)       |
| `irsRetention`      | Portuguese IRS withholding flag        |
| `emailNotification` | Auto-send invoices via email           |
| `defaultPayDue`     | Default payment due, a [`DueDateTerm`](../api-reference.md#duedateterm). On Cegid Vendus, `Days0` is sent as `now` |
| `discount`          | Discount percentage (0 to 100)         |
| `creditLimit`       | Credit limit (0 or more)               |
| `deliveryMethodId`  | Provider delivery method id (0 or more) |
| `reference`         | Your own code for the client. Sent as Cegid Vendus `external_reference` on invoices, and as Moloni `number` on create |

> [!NOTE]
> Required fields vary by provider. If a required field is missing, the provider will return an explicit error message.

### Moloni

- `name`, `vat`, `address` and `city` are required; a missing one throws `MissingClientDataException` before the request.
- `reference` is sent as the Moloni customer `number`. Moloni requires it on create, and the package does not generate one: without `reference`, no `number` is sent and Moloni rejects the request. `id` is not used for it.
- After `execute()`, `id` holds the Moloni `customer_id`, the one `Client::get()` expects.
- Moloni stores at most 20 characters of the `number` and cuts a longer one without an error. The returned `reference` keeps the full value, so it no longer matches the stored number and `Client::find()` by that `reference` returns nothing.
- `country` is turned into the Moloni `country_id` with a `countries/getAll` lookup. Without it, Portugal (`PT`) is used. An ISO code Moloni does not know throws `CouldNotGetCountryIdException`.
- `language` is turned into the Moloni `language_id` with a `languages/getAll` lookup. Without it, Portuguese (`pt`) is used. A code Moloni does not know throws `CouldNotGetLanguageIdException`.
- `defaultPayDue` is sent as the Moloni `maturity_date_id`: the account due date with the same or closest number of days (a tie picks the shorter one), found with a `maturityDates/getAll` lookup. Without it, `DueDateTerm::Days15` is used. When the account has no due dates, `0` is sent. `payment_day` is sent as `0`.
- `discount`, `creditLimit` and `deliveryMethodId` are sent as `discount`, `credit_limit` and `delivery_method_id`. Without them, `0` is used. Cegid Vendus does not send them.
- `irsRetention` and `emailNotification` are not sent. The payment method and salesman ids are sent as `0` for now.

### Cegid Vendus

- Fields you do not set are not sent.
- `irsRetention` and `emailNotification` are sent as `irs_retention` and `send_email` (`yes` or `no`) only when you set them. Without them, Cegid Vendus stores `no`.

## Complete Example

Here's a full example with all available fields:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Facades\Client;

// Collect customer information from your form
$clientData = ClientData::make([
    'name' => 'Empresa ABC - Comércio de Eletrónica Lda',
    'vat' => '501234567',
    'email' => 'accounting@empresaabc.pt',
    'phone' => '210000000',
    'address' => 'Avenida da República, 50 - 3º Andar',
    'city' => 'Lisboa',
    'postalCode' => '1050-190',
    'country' => 'PT',
    'defaultPayDue' => DueDateTerm::Days30,
    'emailNotification' => true,
    'notes' => 'Contract #2025-001',
]);

// Register with the invoicing provider
$client = Client::create($clientData)->execute()->getClient();

// Store in your database for future reference
$customer = new Customer();
$customer->company_name = $client->name;
$customer->vat_number = $client->vat;
$customer->provider_client_id = $client->id;
$customer->save();
```

Example provider response:

```json
{
    "id": 54321,
    "name": "Empresa ABC - Comércio de Eletrónica Lda",
    "vat": "501234567",
    "email": "accounting@empresaabc.pt",
    "phone": "210000000",
    "address": "Avenida da República, 50 - 3º Andar",
    "city": "Lisboa",
    "postalCode": "1050-190",
    "country": "PT",
    "defaultPayDue": 30,
    "emailNotification": true
}
```

## Using Created Clients for Orders

Once registered, use the client for all their invoices:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Option 1: Create and immediately use
$client = Client::create($clientData)->execute()->getClient();

$invoiceData = InvoiceData::make([
    'client' => $client,
    'items' => [
        ItemData::make([
            'reference' => 'LAPTOP-BULK',
            'price' => 75000,
            'quantity' => 20,
        ]),
    ],
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();

// Option 2: Use stored client ID later
$storedClientId = $customer->provider_client_id;
$client = Client::get(ClientData::make(['id' => $storedClientId]))->execute()->getClient();

$invoiceData = InvoiceData::make([
    'client' => $client,
    // ... add items, payments, etc.
]);
Invoice::create($invoiceData)->execute();
```

## Handling Duplicate Clients

Providers reject a client whose VAT number already exists, and the package throws `RequestFailedException`. The message comes from the provider and differs per provider, so do not match on it. Look the client up by VAT instead:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

$clientData = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => '509876543',
    'address' => 'Rua das Flores 125',
    'city' => 'Porto',
    'email' => 'invoices@techstore.pt',
]);

try {
    $client = Client::create($clientData)->execute()->getClient();
} catch (RequestFailedException $e) {
    $client = Client::find(ClientData::make(['vat' => '509876543']))->execute()->getList()->first();

    throw_if(is_null($client), $e);
}
```

## Error Handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;

try {
    $client = Client::create($clientData)->execute()->getClient();
} catch (UnauthorizedException $e) {
    // Invalid API credentials - check your .env
    Log::error('Invalid API credentials', ['error' => $e->getMessage()]);
} catch (RequestFailedException $e) {
    // Provider rejected the request
    Log::error('Client creation failed', ['error' => $e->getMessage()]);
    // Common causes: duplicate client, invalid data, missing required fields
}
```

---

Store the provider-assigned `id` in your database for future reference. Use `emailNotification: true` to have the provider send invoices automatically and `defaultPayDue` to set payment terms (e.g., `DueDateTerm::Days30`). Country codes must be valid ISO 3166-1 alpha-2 codes. The `notes` field is internal-only and not shown on invoices.

---

Next: [Getting a Client](getting-a-client.md)
