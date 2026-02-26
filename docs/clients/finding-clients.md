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

## Filtering by Email

Cegid Vendus currently supports server-side email filtering:

```php
$filters = ClientData::make(['email' => 'billing@example.com']);

$results = Client::find($filters)->execute();
```

Filterable fields depend on the provider, but the `ClientData` DTO supports
common attributes such as `email`, `vat`, `status`, and `externalReference`.

## Pagination API

`Client::find()` implements `next()`, `previous()`, and `page()` helpers through
the `HasPaginator` trait.

```php
$results = Client::find()->execute();

while ($results->getCurrentPage() < $results->getTotalPages()) {
    // do stuff

    // Move to the next provider page
    $results->next()->execute();
}
```

- `getTotalPages()` reads the provider's `X-Paginator-Pages` header (defaults to
  `1` if absent).
- Calling `next()` beyond the last page, `previous()` before page 1, or
  `page()` with an invalid value raises `NoMorePagesException`.

## Putting It Together

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;

try {
    $filters = ClientData::make(['email' => 'acme.com']);
    $clients = Client::find($filters)->execute();

    do {
        foreach ($clients->getList() as $client) {
            // do stuff
        }

        if ($clients->getCurrentPage() < $clients->getTotalPages()) {
            $clients->next()->execute();
        }
    } while ($clients->getCurrentPage() < $clients->getTotalPages());
} catch (NoMorePagesException $e) {
    report($e);
}
```

## Reference

See also:

- [Clients Overview](README.md)
- [Creating a Client](creating-a-client.md)
- [Getting a Client](getting-a-client.md)
- [API Reference – FindClient](../api-reference.md#createclient-contract)
