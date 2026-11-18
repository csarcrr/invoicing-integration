# Getting a Category

Use `Category::fetch()` to fetch one category by its provider ID.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Facades\Category;

$category = Category::fetch(CategoryData::make(['id' => 10]))->execute()->get();

$category->id;
$category->name;
$category->parentId; // null for a top-level category and on Vendus
```

- Without an `id`, `execute()` throws `InvalidArgumentException` with `Category ID is required.`
- When the provider has no category with that ID, `execute()` throws `RequestFailedException`.

## Vendus

The request is sent to `GET products/categories/{id}`. `id` is mapped from `id` and `name` from `title`. `parentId` is always `null`.

## Moloni

The request is sent to `productCategories/getOne` with `category_id`. `id` is mapped from `category_id`, `name` from `name` and `parentId` from `parent_id` (`0` becomes `null`).

## Reference

See also:

- [Categories Overview](README.md)
- [Finding Categories](finding-categories.md)
- [API Reference – ShouldFetchCategory](../api-reference.md#shouldfetchcategory-contract)
