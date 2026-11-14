# Deleting a Client

Use `Client::delete()` to delete a stored client, by its provider ID.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

Client::delete(ClientData::make(['id' => '12345']))->execute();
```

- Pass the provider client ID (`id`).
- `execute()` returns nothing. Check the outcome through the exceptions below.
- Without an `id`, `execute()` throws `InvalidArgumentException` with `Client ID is required.`
- When the provider rejects the deletion, `execute()` throws `RequestFailedException`.

## Cegid Vendus

The request is sent to `DELETE clients/{id}` with no body.

## Moloni

The request is sent to `customers/delete` with `customer_id`.

Moloni returns `valid` and `message`. When `valid` is not `1`, `execute()` throws `RequestFailedException` with the provider `message`.

> [!NOTE]
> Moloni cannot delete a client that a document references. The provider answers `valid: 0`, and the package throws `RequestFailedException` with the provider message.

## Reference

See also:

- [Clients Overview](README.md)
- [Updating a Client](updating-a-client.md)
- [API Reference – ShouldDeleteClient](../api-reference.md#shoulddeleteclient-contract)
