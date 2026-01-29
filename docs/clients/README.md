# Clients

Manage clients in your invoicing provider using the Client API. Clients can be created and retrieved independently of invoices, allowing you to maintain a client database in the provider system.

## Available Operations

- [Creating a Client](creating-a-client.md) - Register new clients in the provider
- [Getting a Client](getting-a-client.md) - Retrieve existing client information
- [Finding Clients](finding-clients.md) - Search & paginate provider clients

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

// Create a new client (hydrated through spatie/laravel-data)
$clientData = ClientData::from([
    'name' => 'John Doe',
    'vat' => '123456789',
    'email' => 'john@example.com',
]);

$client = Client::create($clientData)->execute();

echo $client->getId(); // Provider-assigned ID

// Later, retrieve the client
$existingClient = ClientData::from(['id' => $client->getId()]);
$fetched = Client::get($existingClient)->execute();

echo $fetched->getName(); // "John Doe"
```

### Provider-Specific Properties

Some providers (like Cegid Vendus) return fields that are not explicitly mapped
on `ClientData` (e.g., `price_group`, `status`, `balance`). Because the value
object is powered by `spatie/laravel-data`, you can hydrate it via
`ClientData::from()` and any unmapped attributes are retained in the
`additional` bag. They also appear automatically when you call `toArray()`:

```php
$extra = $fetched->getAdditionalData();

$status = $extra['status'] ?? null;
$priceGroup = $extra['price_group']['name'] ?? null;
```

Use this to inspect provider metadata without polluting the public API surface.

## When to Use Client Management

**Create clients when:**

- You want to maintain a persistent client database in the provider
- You need to reuse client information across multiple invoices
- You want to leverage provider-specific client features (e.g., email notifications, payment terms)

**Use inline client data when:**

- You only need the client for a single invoice
- You prefer not to create records in the provider's client database

> **Note:** You can still pass a `ClientData` object to `Invoice::create()->client()` without first creating the client in the provider. The invoice will be issued with the client details embedded directly.
