# Categories

A category is an account-level record stored in the provider (for example "Produtos" or "Serviços"). Items reference a category through `ItemData->category`.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\Category;

foreach (Category::find()->execute()->getList() as $category) {
    echo $category->id.' '.$category->name;
}
```

## Available operations

| Operation         | Docs                                        |
| ----------------- | ------------------------------------------- |
| Create a category | [Creating a Category](creating-a-category.md) |
| Find categories   | [Finding Categories](finding-categories.md) |
| Get a category    | [Getting a Category](getting-a-category.md) |

## Provider support

| Operation       | Cegid Vendus | Moloni | Invoice Express |
| --------------- | ------------ | ------ | --------------- |
| Create Category | ✅           | ✅     | ❌              |
| Find Categories | ✅           | ✅     | ❌              |
| Get Category    | ✅           | ✅     | ❌              |
