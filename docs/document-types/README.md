# Document Types

A document type is a kind of fiscal document stored in the provider, identified by a code such as `FT` (Fatura) or `NC` (Nota de Crédito). Each one has a `code` and a `name`.

> [!NOTE]
> `DocumentTypeData` and the `DocumentType` facade list the document types the provider knows. They do not change which documents the package can issue: those are the `InvoiceType` enum cases.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\DocumentType;

foreach (DocumentType::find()->execute()->getList() as $type) {
    echo $type->code.' '.$type->name;
}
```

## Available operations

| Operation           | Docs                                                |
| ------------------- | --------------------------------------------------- |
| Find document types | [Finding Document Types](finding-document-types.md) |

## Provider support

| Operation           | Cegid Vendus | Moloni | Invoice Express |
| ------------------- | ------------ | ------ | --------------- |
| Find Document Types | ✅           | ✅     | ❌              |
