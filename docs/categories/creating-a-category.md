# Creating a Category

Use `Category::create()` to create a product category in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Facades\Category;

$category = Category::create(CategoryData::make(['name' => 'Produtos', 'parentId' => 10]))->execute()->getCategory();

$category->id;
$category->name;
$category->parentId; // null on Cegid Vendus
```

- `getCategory()` returns the `CategoryData` with the provider-assigned `id`.
- When the provider does not create the category, `execute()` throws `RequestFailedException` with the provider's error message.

## Cegid Vendus

The request is sent to `POST products/categories`. `name` is sent as `title`. `parentId` is not supported by Cegid Vendus and is ignored.

The returned category is mapped from the response: `id` from `id` and `name` from `title`. `parentId` is always `null`.

## Moloni

The request is sent to `productCategories/insert`. `name` is sent as `name` and `parentId` as `parent_id`. When `parentId` is not set, `0` is sent, which creates a top-level category.

The returned category is mapped from the response: `id` from `category_id`. `name` and `parentId` are kept from the input. A response whose `valid` is not `1` throws `RequestFailedException` with the provider's `message`.

## Reference

See also:

- [Categories Overview](README.md)
- [Getting a Category](getting-a-category.md)
- [API Reference – ShouldCreateCategory](../api-reference.md#shouldcreatecategory-contract)
