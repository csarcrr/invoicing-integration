# Finding Clients

Search and paginate clients stored in the provider without leaving your Laravel
app. The `Client::find()` fluent builder wraps provider-specific filters while
giving you a consistent pagination workflow.

## When to Use

Use `find()` when you need to:

- Display a client picker sourced from the provider
- Audit existing provider records before creating duplicates
- Sync clients from the provider back into your application

If you already know the provider ID, prefer `Client::get()` for a direct lookup.

## Basic Usage

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

$results = Client::find()->execute();

foreach ($results->getList() as $client) {
    // do stuff
}
```

- `getList()` returns a `Collection` of `ClientData` instances populated by
  the provider response.
- The same paginator object is returned, so you can immediately call
  `next()->execute()` to fetch additional pages.
- On Cegid Vendus, each returned client has the same fields as a client from
  `Client::get()`, including `date`.
- Returned clients do not carry `getAdditionalData()`.

## Filtering by Email

Cegid Vendus and Moloni support server-side email filtering:

```php
$filters = ClientData::make(['email' => 'billing@example.com']);

$results = Client::find($filters)->execute();
```

Moloni matches part of the value and ignores case. Cegid Vendus matches the
whole value: `email` must be the full address with the same case, and `name`
the full name in any case.

Filterable fields depend on the provider, but the `ClientData` DTO supports
common attributes such as `email`, `vat`, `status`, and `externalReference`.

## Searching on Moloni

With no filter set, Moloni lists all clients. With filters set, Moloni searches
by one field only. When several are set, the package sends the first one set,
in this order:

1. `vat`
2. `email`
3. `id` (sent as the customer number)
4. `name`

```php
$results = Client::find(ClientData::make(['vat' => '215783920']))->execute();
```

- Other filters (`status`, `externalReference`, `date`) are ignored.
- Each returned client has `id` set to the Moloni `customer_id`, ready for
  `Client::get()`.

## Searching on Cegid Vendus

Cegid Vendus combines every filter set. The package sends only the fields
Cegid Vendus can search by:

- `vat` (sent as `fiscal_id`)
- `name`
- `email`
- `externalReference`
- `status`
- `date` (sent as `Y-m-d`)

```php
$results = Client::find(ClientData::make(['name' => 'Acme', 'status' => 'active']))->execute();
```

- Other filters (`id`, `city`, `address`, `phone`, `notes`, `country`,
  `irsRetention`, ...) are not sent.
- When nothing matches, Cegid Vendus answers `404` with error code `A001`
  (`No data`). The package returns an empty list instead of throwing. Any
  other `404` throws.

## Pagination API

`Client::find()` implements `next()`, `previous()`, and `page()` helpers through
the `HasPaginator` trait.

```php
$results = Client::find()->execute();

while ($results->getList()->isNotEmpty()) {
    // do stuff

    if ($results->getCurrentPage() === $results->getTotalPages()) {
        break;
    }

    $results->next()->execute();
}
```

- `getTotalPages()` reads the provider's `X-Paginator-Pages` header (defaults to
  `1` if absent). It returns `null` when the provider does not report a total
  (Moloni), and before the first `execute()`: keep calling `next()` until a page
  comes back empty.
- Calling `previous()` before page 1, or `page()` below 1, raises
  `NoMorePagesException`. On Cegid Vendus, after the first `execute()`,
  `next()` beyond the last page and `page()` above the total also raise it. On
  Moloni they return an empty list.

## Putting It Together

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

$filters = ClientData::make(['email' => 'billing@example.com']);
$clients = Client::find($filters)->execute();

while ($clients->getList()->isNotEmpty()) {
    foreach ($clients->getList() as $client) {
        // do stuff
    }

    if ($clients->getCurrentPage() === $clients->getTotalPages()) {
        break;
    }

    $clients->next()->execute();
}
```

## Reference

See also:

- [Clients Overview](README.md)
- [Creating a Client](creating-a-client.md)
- [Getting a Client](getting-a-client.md)
- [API Reference – FindClient](../api-reference.md#shouldfindclient-contract)
