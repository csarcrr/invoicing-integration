# Creating a Client

Register clients in your invoicing provider using `Client::create()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

$clientData = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => 'PT509876543',
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
    "vat": "PT509876543",
    "email": "invoices@techstore.pt"
}
```

## ClientData Properties

Build complete customer profiles with all available fields:

```php
$clientData = ClientData::make([
    // Business identity
    'name' => 'Distribuidor Norte Lda',
    'vat' => 'PT509999888',

    // Contact information
    'email' => 'finance@distrinorte.pt',
    'phone' => '220123456',

    // Billing address
    'address' => 'Zona Industrial de Maia, Lote 42',
    'city' => 'Maia',
    'postalCode' => '4470-000',
    'country' => 'PT',

    // Billing settings
    'defaultPayDue' => 30,          // NET30 payment terms
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
| `notes`             | Internal notes (not on invoices)       |
| `irsRetention`      | Portuguese IRS withholding flag        |
| `emailNotification` | Auto-send invoices via email           |
| `defaultPayDue`     | Default payment due (days)             |

> **Note:** Required fields vary by provider. If a required field is missing, the provider will return an explicit error message.

## Complete Example

Here's a full example with all available fields:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

// Collect customer information from your form
$clientData = ClientData::make([
    'name' => 'Empresa ABC - Comércio de Eletrónica Lda',
    'vat' => 'PT501234567',
    'email' => 'accounting@empresaabc.pt',
    'phone' => '210000000',
    'address' => 'Avenida da República, 50 - 3º Andar',
    'city' => 'Lisboa',
    'postalCode' => '1050-190',
    'country' => 'PT',
    'defaultPayDue' => 30,
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
    "vat": "PT501234567",
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
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Option 1: Create and immediately use
$client = Client::create($clientData)->execute()->getClient();

$invoice = Invoice::create();
$invoice->client($client);
$invoice->item(ItemData::make([
    'reference' => 'LAPTOP-BULK',
    'price' => 75000,
    'quantity' => 20,
]));
$result = $invoice->execute()->getInvoice();

// Option 2: Use stored client ID later
$storedClientId = $customer->provider_client_id;
$client = Client::get(ClientData::make(['id' => $storedClientId]))->execute()->getClient();

$invoice = Invoice::create();
$invoice->client($client);
// ... add items, execute
```

## Handling Duplicate Clients

Some providers reject duplicate VAT numbers. Handle this gracefully:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

$clientData = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => 'PT509876543',
    'email' => 'invoices@techstore.pt',
]);

try {
    $client = Client::create($clientData)->execute()->getClient();
} catch (RequestFailedException $e) {
    // Check if it's a duplicate error
    if (str_contains($e->getMessage(), 'duplicate') || str_contains($e->getMessage(), 'already exists')) {
        // Search for existing client
        $results = Client::find(ClientData::make(['vat' => 'PT509876543']))->execute();
        $client = $results->getList()->first();
    } else {
        throw $e;
    }
}

// Now you have the client, whether new or existing
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

**Tips:**

- Store the provider-assigned `id` in your database for future reference
- Use `emailNotification: true` to have the provider send invoices automatically
- Country codes must be valid ISO 3166-1 alpha-2 codes
- Use `defaultPayDue` to set payment terms (e.g., 30 days)
- The `notes` field is internal-only (not shown on invoices)

---

Next: [Getting a Client](getting-a-client.md)
