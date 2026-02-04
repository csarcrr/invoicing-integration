# Clients

Manage clients in your invoicing provider using the Client API. Clients can be created and retrieved independently of invoices, allowing you to maintain a client database in the provider system.

## Available Operations

- [Creating a Client](creating-a-client.md) - Register new clients in the provider
- [Getting a Client](getting-a-client.md) - Retrieve existing client information
- [Finding Clients](finding-clients.md) - Search & paginate provider clients

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

// Create a new client (hydrated through spatie/laravel-data)
$clientData = ClientData::make([
    'name' => 'John Doe',
    'vat' => '123456789',
    'email' => 'john@example.com',
]);

$client = Client::create($clientData)->execute();

// Later, retrieve the client
$existingClient = ClientData::make(['id' => $client->id]);
$fetched = Client::get($existingClient)->execute();
```

Example client payload (`$fetched->toArray()`):

```json
{
    "id": 98765,
    "name": "John Doe",
    "vat": "PT123456789",
    "email": "john@example.com"
}
```

> `ClientData` and the other DTOs expose **typed public properties**. Access
> values directly via `$client->name`, `$client->vat`, etc. instead of calling
> getters.

### Provider-Specific Properties

Some providers (like Cegid Vendus) return fields that are not explicitly mapped
on `ClientData` (e.g., `price_group`, `status`, `balance`). Because the value
object is powered by `spatie/laravel-data`, you can hydrate it via
`ClientData::make()` and any unmapped attributes are retained in the
`additional` bag. They also appear automatically when you call `toArray()`:

```php
$payload = $fetched->toArray();

$status = $payload['status'] ?? null;
$priceGroup = $payload['price_group']['name'] ?? null;
```

Use `toArray()` to inspect provider metadata without polluting the public API
surface. Anything not mapped to a public property remains available in the
generated array.

## When to Use Client Management

**Create clients when:**

- You want to maintain a persistent client database in the provider
- You need to reuse client information across multiple invoices
- You want to leverage provider-specific client features (e.g., email notifications, payment terms)

**Use inline client data when:**

- You only need the client for a single invoice
- You prefer not to create records in the provider's client database

> **Note:** You can still pass a `ClientData` object to `Invoice::create()->client()` without first creating the client in the provider. The invoice will be issued with the client details embedded directly.
