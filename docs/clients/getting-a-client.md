# Getting a Client

Retrieve an existing client from your invoicing provider using `Client::get()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\ClientData;

// Create a ClientData object with the known ID
$clientData = ClientData::id(12345);

// Fetch the client from the provider
$client = Client::get($clientData)->execute();

// Access client properties
echo $client->getName();    // "John Doe"
echo $client->getEmail();   // "john@example.com"
echo $client->getVat();     // "123456789"
```

## Requirements

The `id` property is **required** when retrieving a client:

```php
// This will throw an InvalidArgumentException
$clientData = new ClientData;
Client::get($clientData)->execute(); // Error: Client ID is required.

// Correct usage
$clientData = ClientData::id(12345);
Client::get($clientData)->execute(); // Works
```

## Retrieved Properties

After calling `execute()`, the `ClientData` object is populated with all available data from the provider:

```php
$clientData = ClientData::id(12345);
$client = Client::get($clientData)->execute();

// All properties are now available
$client->getId();               // 12345
$client->getName();             // Client name
$client->getVat();              // Tax ID / Fiscal ID
$client->getEmail();            // Email address
$client->getPhone();            // Phone number
$client->getAddress();          // Street address
$client->getCity();             // City
$client->getPostalCode();       // Postal code
$client->getCountry();          // Country code
$client->getNotes();            // Internal notes
$client->getIrsRetention();     // IRS retention flag
$client->getEmailNotification();// Email notification flag
$client->getDefaultPayDue();    // Default payment due days
```

> **Note:** Properties not set in the provider will return `null`.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\ClientData;

// Assume you stored the client ID from a previous create operation
$storedClientId = 12345;

// Retrieve the client
$clientData = ClientData::id($storedClientId);
$client = Client::get($clientData)->execute();

// Display client information
echo "Name: " . $client->getName() . "\n";
echo "VAT: " . $client->getVat() . "\n";
echo "Email: " . $client->getEmail() . "\n";
echo "Address: " . $client->getAddress() . "\n";
echo "City: " . $client->getCity() . "\n";
echo "Country: " . $client->getCountry() . "\n";
```

## Using Retrieved Clients with Invoices

You can use a retrieved client when creating invoices:

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

// Retrieve existing client
$clientData = ClientData::id(12345);
$client = Client::get($clientData)->execute();

// Use client in invoice
$invoice = Invoice::create();
$invoice->client($client);

$item = (new Item())->reference('SKU-001')->price(1000);
$invoice->item($item);

$result = $invoice->execute();
```

## Error Handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use InvalidArgumentException;

try {
    $clientData = ClientData::id(12345);
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
