# Updating a Category

Use `Category::update()` to change the name or the parent of a stored category in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Facades\Category;

$category = Category::update(CategoryData::make(['id' => 29999, 'name' => 'Bebidas', 'parentId' => 10]))->execute()->getCategory();

$category->id;
$category->name;
$category->parentId; // null on Cegid Vendus
```

- `getCategory()` returns the `CategoryData` you passed in on Moloni, and the category as stored after the update on Cegid Vendus.
- When the provider does not update the category, `execute()` throws `RequestFailedException` with the provider's error message.

## Requirements

The `id` property is **required** when updating a category. It is the provider category id, the one `Category::get()` expects.

```php
// This will throw an InvalidArgumentException
Category::update(CategoryData::make(['name' => 'Bebidas']));  // Error: Category ID is required.

// Correct usage
Category::update(CategoryData::make(['id' => 29999, 'name' => 'Bebidas']));
```

## Updated fields

| Field      | Moloni                                | Cegid Vendus               |
| ---------- | ------------------------------------- | -------------------------- |
| `id`       | `category_id` (required)              | Path `products/categories/{id}` |
| `name`     | `name`, sent only when set            | `title`, sent only when set |
| `parentId` | `parent_id`, sent only when set       | not sent                   |

Fields you do not set are not sent, so the provider keeps their stored values.

## Cegid Vendus

The request is sent as `PATCH products/categories/{id}`. `name` is sent as `title`. `parentId` is not supported by Cegid Vendus and is ignored.

The returned category is mapped from the response: `id` from `id` and `name` from `title`. `parentId` is always `null`.

## Moloni

The request is sent as `POST productCategories/update`, with `category_id` in the body.

Moloni requires `parent_id` on update, and the package sends it only when `parentId` is set. Set `parentId` to the parent category id, or to `0` for a top-level category. When `parentId` is not set, the request is sent without `parent_id` and Moloni answers with its own error, which `execute()` throws as `RequestFailedException`.

The returned category is the `CategoryData` you passed in. A response whose `valid` is not `1` throws `RequestFailedException` with the provider's `message`.

## Reference

See also:

- [Categories Overview](README.md)
- [Creating a Category](creating-a-category.md)
- [Getting a Category](getting-a-category.md)
- [API Reference – ShouldUpdateCategory](../api-reference.md#shouldupdatecategory-contract)
