# Clients

Manage clients in your invoicing provider using the Client API. Clients can be created and retrieved independently of invoices, allowing you to maintain a client database in the provider system.

## Available Operations

- [Creating a Client](creating-a-client.md) - Register new clients in the provider
- [Getting a Client](getting-a-client.md) - Retrieve existing client information

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

// Create a new client
$clientData = (new ClientData())
    ->name('John Doe')
    ->vat('123456789')
    ->email('john@example.com');

$client = Client::create($clientData)->execute();

echo $client->getId(); // Provider-assigned ID

// Later, retrieve the client
$existingClient = (new ClientData())->id($client->getId());
$fetched = Client::get($existingClient)->execute();

echo $fetched->getName(); // "John Doe"
```

## When to Use Client Management

**Create clients when:**

- You want to maintain a persistent client database in the provider
- You need to reuse client information across multiple invoices
- You want to leverage provider-specific client features (e.g., email notifications, payment terms)

**Use inline client data when:**

- You only need the client for a single invoice
- You prefer not to create records in the provider's client database

> **Note:** You can still pass a `ClientData` object to `Invoice::create()->client()` without first creating the client in the provider. The invoice will be issued with the client details embedded directly.
