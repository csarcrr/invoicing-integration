# Finding Companies

Use `Company::find()` to list the companies the provider user has access to.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\Company;

$results = Company::find()->execute();

foreach ($results->getList() as $company) {
    $company->id;
    $company->name;
    $company->vat;
}
```

- `getList()` returns a `Collection` of `CompanyData` instances.
- `Company::find()` takes no filters and has no pagination: the provider returns every company in one response.

## Moloni

The request is sent to `companies/getAll`. It does not depend on the configured `company_id`, so it works before one is set. Each row is mapped as:

| Moloni field | `CompanyData` |
| ------------ | ------------- |
| `company_id` | `id`          |
| `name`       | `name`        |
| `vat`        | `vat`         |

## Cegid Vendus

Not supported: `Company::find()` throws `OperationNotSupportedException`.

## Reference

See also:

- [Companies Overview](README.md)
- [API Reference – ShouldFindCompany](../api-reference.md#shouldfindcompany-contract)
