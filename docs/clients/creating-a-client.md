# Creating a Client

Register a new client in your invoicing provider using `Client::create()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Facades\Client;

$clientData = ClientData::make([
    'name' => 'John Doe',
    'vat' => '123456789',
]);

$client = Client::create($clientData)->execute();

$clientId = $client->id; // Provider-assigned ID (e.g., 12345)
```

Example response (`$client->toArray()`):

```json
{
    "id": 12345,
    "name": "John Doe",
    "vat": "PT123456789"
}
```

## ClientData Properties

The `ClientData` facade supports the following properties:

```php
$clientData = ClientData::make([
    'name' => 'John Doe',          // Client name
    'vat' => '123456789',          // Tax identification number
    'email' => 'john@example.com', // Email address (validated)
    'phone' => '220123456',        // Phone number
    'address' => 'Rua das Flores 125',
    'city' => 'Porto',
    'postalCode' => '4410-000',
    'country' => 'PT',
    'notes' => 'VIP customer',
    'irsRetention' => true,
    'emailNotification' => true,
    'defaultPayDue' => 30,
]);
```

### Available Fields

| Field               | Description                            |
| ------------------- | -------------------------------------- |
| `name`              | Client display name                    |
| `vat`               | Tax ID / Fiscal ID                     |
| `email`             | Email address (validated format)       |
| `phone`             | Phone number                           |
| `address`           | Street address                         |
| `city`              | City name                              |
| `postalCode`        | Postal/ZIP code                        |
| `country`           | ISO 3166-1 alpha-2 code (e.g., PT, ES) |
| `notes`             | Internal notes (not shown on invoices) |
| `irsRetention`      | Portuguese IRS withholding flag        |
| `emailNotification` | Auto-send documents via email          |
| `defaultPayDue`     | Default payment due days               |

> **Note:** Required fields vary by provider. Check your provider's documentation for specific requirements. If a required field is missing, the request will fail with an explicit error message from the provider.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Facades\Client;

// Build client data
$clientData = ClientData::make([
    'name' => 'Acme Corporation',
    'vat' => 'PT501234567',
    'email' => 'billing@acme.example.com',
    'phone' => '210000000',
    'address' => 'Avenida da Liberdade, 100',
    'city' => 'Lisboa',
    'postalCode' => '1250-096',
    'country' => 'PT',
    'irsRetention' => false,
    'emailNotification' => true,
    'defaultPayDue' => 30,
]);

// Create client in provider
$client = Client::create($clientData)->execute();

// The client now has an ID assigned by the provider
$clientId = $client->id;
```

Example provider payload:

```json
{
    "id": 54321,
    "name": "Acme Corporation",
    "vat": "PT501234567",
    "email": "billing@acme.example.com",
    "phone": "210000000",
    "address": "Avenida da Liberdade, 100",
    "city": "Lisboa",
    "postalCode": "1250-096",
    "country": "PT",
    "defaultPayDue": 30,
    "emailNotification": true
}
```

## Using Created Clients with Invoices

Once a client is created, you can use it when issuing invoices:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Create or retrieve client
$clientData = ClientData::make([
    'name' => 'Acme Corporation',
    'vat' => 'PT501234567',
]);

$client = Client::create($clientData)->execute();

// Use client in invoice
$invoice = Invoice::create();
$invoice->client($client);

$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000,
]);
$invoice->item($item);

$result = $invoice->execute();
```

## Error Handling

Client creation may fail due to provider-specific validation:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;

try {
    $client = Client::create($clientData)->execute();
} catch (UnauthorizedException $e) {
    // Invalid API credentials
} catch (RequestFailedException $e) {
    // Provider rejected the request (e.g., duplicate client, invalid data)
}
```

---

**Tips:**

- Store the provider-assigned `id` in your database for future reference
- Use `emailNotification(true)` to have the provider send documents automatically
- Country codes must be valid ISO 3166-1 alpha-2 codes

---

Next: [Getting a Client](getting-a-client.md)
