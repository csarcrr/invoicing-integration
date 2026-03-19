# Finding Items

Search and paginate items stored in the provider's catalog without leaving your
Laravel app. The `Item::find()` fluent builder wraps provider-specific filters
while giving you a consistent pagination workflow.

## When to Use

Use `find()` when you need to:

- Search the provider's product catalog before creating duplicates
- Display an item picker sourced from the provider
- Sync catalog items from the provider back into your application

If you already know the provider ID, prefer `Item::get()` for a direct lookup.

## Basic Usage

```php
use CsarCrr\InvoicingIntegration\Facades\Item;

$results = Item::find()->execute();

foreach ($results->getList() as $item) {
    // do stuff
}
```

- `getList()` returns a `Collection` of `ItemData` instances populated by the
  provider response.
- The same paginator object is returned, so you can immediately call
  `next()->execute()` to fetch additional pages.

## Filtering

Pass an `ItemData` with one or more search fields to narrow results:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

// Filter by name
$filters = ItemData::make(['name' => 'Widget']);
$results = Item::find($filters)->execute();

// Filter by product reference (SKU)
$filters = ItemData::make(['reference' => 'WGT-001']);
$results = Item::find($filters)->execute();

// Filter by barcode
$filters = ItemData::make(['barcode' => '5601234567890']);
$results = Item::find($filters)->execute();
```

Supported filter fields on `ItemData` for Cegid Vendus:

| Field       | Type      | Description               |
| ----------- | --------- | ------------------------- |
| `name`      | `?string` | Product name              |
| `reference` | `?string` | Product SKU / code        |
| `barcode`   | `?string` | EAN or barcode identifier |

> [!NOTE]
> Cegid Vendus maps `name` to the `title` query parameter internally. This is
> handled transparently by the package — pass `name` on `ItemData` as usual.

## Pagination API

`Item::find()` implements `next()`, `previous()`, and `page()` helpers through
the `HasPaginator` trait.

```php
$results = Item::find()->execute();

while ($results->getCurrentPage() < $results->getTotalPages()) {
    // do stuff

    // Move to the next provider page
    $results->next()->execute();
}
```

- `getTotalPages()` reads the provider's `X-Paginator-Pages` header (defaults
  to `1` if absent).
- Calling `next()` beyond the last page, `previous()` before page 1, or
  `page()` with an invalid value raises `NoMorePagesException`.

## Putting It Together

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\Item;

try {
    $filters = ItemData::make(['name' => 'Widget']);
    $items = Item::find($filters)->execute();

    do {
        foreach ($items->getList() as $item) {
            // do stuff
        }

        if ($items->getCurrentPage() < $items->getTotalPages()) {
            $items->next()->execute();
        }
    } while ($items->getCurrentPage() < $items->getTotalPages());
} catch (NoMorePagesException $e) {
    report($e);
}
```

## Reference

See also:

- [Items Overview](README.md)
- [Creating an Item](creating-an-item.md)
- [Getting an Item](getting-an-item.md)
- [API Reference – ShouldFindItem](../api-reference.md#shouldfinditem-contract)
