# Finding Categories

Use `Category::find()` to list the categories stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Facades\Category;

$results = Category::find()->execute();

foreach ($results->getList() as $category) {
    $category->id;
    $category->name;
    $category->parentId; // null for a top-level category and on Cegid Vendus
}

$subcategories = Category::find(CategoryData::make(['parentId' => 10]))->execute();
```

- `getList()` returns a `Collection` of `CategoryData` instances.
- `parentId` lists the subcategories of that category. Without it, the top-level categories are listed.

## Pagination

`Category::find()` paginates the same way as [`Item::find()`](../items/finding-an-item.md): `next()`, `previous()`, `page()`, `getCurrentPage()` and `getTotalPages()`. Moving outside the available pages throws `NoMorePagesException`.

## Cegid Vendus

The request is sent to `GET products/categories` with `page`. Cegid Vendus categories have no hierarchy, so `parentId` is not sent and is always `null` in the results. `getTotalPages()` reads the `X-Paginator-Pages` header. When Cegid Vendus reports no data, `getList()` is empty.

Each row is mapped as:

| Cegid Vendus field | `CategoryData` |
| ------------------ | -------------- |
| `id`               | `id`           |
| `title`            | `name`         |

Cegid Vendus applies its own default `status` filter, so categories that are turned off may be left out.

## Moloni

The request is sent to `productCategories/getAll` with `parent_id`: the `parentId` you set, or `0` for the top level. Moloni returns every category of that level in one response, so `getTotalPages()` is `1` and `next()` throws `NoMorePagesException`.

Each row is mapped as:

| Moloni field  | `CategoryData` |
| ------------- | -------------- |
| `category_id` | `id`           |
| `name`        | `name`         |
| `parent_id`   | `parentId` (`0` becomes `null`) |

## Reference

See also:

- [Categories Overview](README.md)
- [Getting a Category](getting-a-category.md)
- [API Reference – ShouldFindCategory](../api-reference.md#shouldfindcategory-contract)
