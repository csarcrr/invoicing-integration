# Finding Document Types

Use `DocumentType::find()` to list the document types the provider knows, such as `FT` or `NC`. This list does not change which documents the package can issue: those are the [`InvoiceType`](../api-reference.md#invoicetype) cases.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\DocumentType;

$types = DocumentType::find()->execute()->getList();

foreach ($types as $type) {
    $type->code;                // e.g. FT
    $type->name;                // e.g. Fatura
    $type->getAdditionalData(); // the other fields the provider returns
}
```

- `getList()` returns a `Collection` of `DocumentTypeData` instances.
- `DocumentType::find()` takes no filters and has no pagination: the provider returns every document type in one response.
- The extra fields the provider returns are kept as additional data, read with `getAdditionalData()`.

## Cegid Vendus

The request is sent to `GET documents/types`. Each row is mapped as:

| Cegid Vendus field | `DocumentTypeData` |
| ------------------ | ------------------ |
| `id`               | `code`             |
| `title`            | `name`             |

The `visible_filter` and `visible_finalize` fields are kept as additional data, read with `getAdditionalData()`. The endpoint's `visible_filter` and `visible_finalize` filters are not exposed. Cegid Vendus has no language parameter, so a `LanguageData` passed to `find()` is ignored. When Cegid Vendus reports no data, `getList()` is empty.

## Moloni

The request is sent to `documents/getAllDocumentTypes`. Each row is mapped as:

| Moloni field | `DocumentTypeData` |
| ------------ | ------------------ |
| `saft_code`  | `code`             |
| `name`       | `name`             |

The Moloni `document_type_id` is available through `getAdditionalData()`.

Moloni requires a language. When `execute()` runs, the package looks up the language ID from the code of the `LanguageData` with one extra request to `languages/getAll`, and sends it with the request. Without a language, `pt` is used. Pass a language to get the names in another language:

```php
use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Facades\DocumentType;

$types = DocumentType::find(LanguageData::from(['code' => 'en']))->execute()->getList();
```

An unknown language code throws `CouldNotGetLanguageIdException`. Before `execute()`, `getPayload()` is empty on Moloni; the `language_id` is added during `execute()`.

## Reference

See also:

- [Document Types Overview](README.md)
- [API Reference – ShouldFindDocumentType](../api-reference.md#shouldfinddocumenttype-contract)
