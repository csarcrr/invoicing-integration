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
  provider response. Cegid Vendus items carry `id`, `name`, `description`,
  `reference`, `barcode`, `category`, `controlStock`, `enabled`, `type`, `tax`
  and the gross `price`.
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

Supported filter fields on `ItemData` (see [Moloni](#moloni) for how Moloni
combines them):

| Field       | Type      | Description               |
| ----------- | --------- | ------------------------- |
| `name`      | `?string` | Product name              |
| `reference` | `?string` | Product SKU / code        |
| `barcode`   | `?string` | EAN or barcode identifier |
| `category`  | `?CategoryData` | Category to list; see each provider below |

> [!NOTE]
> Cegid Vendus maps `name` to the `title` query parameter internally. This is
> handled transparently by the package — pass `name` on `ItemData` as usual.

## Cegid Vendus

Cegid Vendus sends every filter you set, so several fields narrow the search
together.

- `name`, `reference` and `barcode` match the whole value only. A partial value
  finds nothing.
- A `category` id is sent as `category_id` and narrows the list to that
  category. Without one, all categories are listed.
- The request sends `status=all`, so disabled items are listed too, with
  `enabled` set to `false`.
- When nothing matches, Cegid Vendus answers `404` with error code `A001`
  (`No data`). The package returns an empty list instead of throwing. Any
  other `404` throws.

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

$filters = ItemData::make([
    'reference' => 'WGT-001',
    'category' => CategoryData::make(['id' => 12]),
]);
$results = Item::find($filters)->execute();
```

## Moloni

Moloni searches all products when you set `name`, `reference` or `barcode`,
and matches part of the value. Moloni accepts one search term only. When you set several fields, the package
sends the first one set, in this order: `barcode`, `reference`, `name`. A
search needs no category.

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

$filters = ItemData::make(['reference' => 'WGT-001']);
$results = Item::find($filters)->execute();
```

With no search field set, Moloni lists the items of one category. Pass an
`ItemData` with a `category` id.

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

$filters = ItemData::make(['category' => CategoryData::make(['id' => 12])]);
$results = Item::find($filters)->execute();
```

- With no search field set, a missing category id throws `MissingCategoryException`.
- Each page holds up to 50 items.
- Both a search and a category listing send `with_invisible=1`, so products
  with `visibility_id` `0` are listed too, with `enabled` set to `false`.
- Each item is mapped like [`Item::get()`](getting-an-item.md), including the
  category, description, stock control and stock.
- When nothing matches, the list is empty.

> [!WARNING]
> Moloni documents `with_invisible` for `products/getAll` but not for
> `products/getBySearch`. The package sends it to both because it works on
> both: without it, a search leaves out hidden products.

## Pagination API

`Item::find()` implements `next()`, `previous()`, and `page()` helpers through
the `HasPaginator` trait.

```php
$results = Item::find()->execute();

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
  `NoMorePagesException`. On Cegid Vendus, `next()` beyond the last page and
  `page()` above the total also raise it. On Moloni they return an empty list.

## Putting It Together

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

$filters = ItemData::make(['name' => 'Widget']);
$items = Item::find($filters)->execute();

while ($items->getList()->isNotEmpty()) {
    foreach ($items->getList() as $item) {
        // do stuff
    }

    if ($items->getCurrentPage() === $items->getTotalPages()) {
        break;
    }

    $items->next()->execute();
}
```

## Reference

See also:

- [Items Overview](README.md)
- [Creating an Item](creating-an-item.md)
- [Getting an Item](getting-an-item.md)
- [API Reference – ShouldFindItem](../api-reference.md#shouldfinditem-contract)
