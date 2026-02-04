# Getting a Client

Retrieve an existing client from your invoicing provider using `Client::get()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Facades\Client;

// Create a ClientData object with the known ID
$clientData = ClientData::make(['id' => 12345]);

// Fetch the client from the provider
$client = Client::get($clientData)->execute();

// Access client data via public properties or $client->toArray()
$payload = $client->toArray();
```

Example payload:

```json
{
    "id": 12345,
    "name": "John Doe",
    "vat": "PT123456789",
    "email": "john@example.com"
}
```

## Requirements

The `id` property is **required** when retrieving a client:

```php
// This will throw an InvalidArgumentException
$clientData = ClientData::make([]);
Client::get($clientData)->execute(); // Error: Client ID is required.

// Correct usage
$clientData = ClientData::make(['id' => 12345]);
Client::get($clientData)->execute(); // Works
```

## Retrieved Properties

After calling `execute()`, the `ClientData` object is populated with all available data from the provider:

```php
$clientData = ClientData::make(['id' => 12345]);
$client = Client::get($clientData)->execute();

// All properties are now available via public attributes
$client->id;               // 12345
$client->name;             // Client name
$client->vat;              // Tax ID / Fiscal ID
$client->email;            // Email address
$client->phone;            // Phone number
$client->address;          // Street address
$client->city;             // City
$client->postalCode;       // Postal code
$client->country;          // Country code
$client->notes;            // Internal notes
$client->irsRetention;     // IRS retention flag (bool)
$client->emailNotification;// Email notification flag (bool)
$client->defaultPayDue;    // Default payment due days
```

> **Note:** Properties not set in the provider will return `null`.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Facades\Client;

// Assume you stored the client ID from a previous create operation
$storedClientId = 12345;

// Retrieve the client
$clientData = ClientData::make(['id' => $storedClientId]);
$client = Client::get($clientData)->execute();

// $client now holds the hydrated ClientData instance (see JSON example below)
```

Example provider payload (`$client->toArray()`):

```json
{
    "id": 12345,
    "name": "John Doe",
    "vat": "PT123456789",
    "email": "john@example.com",
    "address": "Av. da Liberdade, 1",
    "city": "Lisbon",
    "country": "PT"
}
```

## Using Retrieved Clients with Invoices

You can use a retrieved client when creating invoices:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Client;use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Retrieve existing client
$clientData = ClientData::make(['id' => 12345]);
$client = Client::get($clientData)->execute();

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

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;use CsarCrr\InvoicingIntegration\Facades\Client;

try {
    $clientData = ClientData::make(['id' => 12345]);
    $client = Client::get($clientData)->execute();
} catch (InvalidArgumentException $e) {
    // Client ID was not provided
} catch (UnauthorizedException $e) {
    // Invalid API credentials
} catch (RequestFailedException $e) {
    // Client not found or provider error
}
```

---

**Tips:**

- Always store the provider-assigned `id` when creating clients for later retrieval
- The returned `ClientData` object is the same instance passed to `get()`, now populated with provider data
- Use retrieved clients to ensure invoice data matches the provider's records

---

Back to: [Clients Overview](README.md)
