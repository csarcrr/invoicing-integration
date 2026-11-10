# Companies

A company is a business the provider account has access to. On Moloni one user can manage several companies, and every request is scoped to the `company_id` set in the config.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\Company;

$companies = Company::find()->execute()->getList();

foreach ($companies as $company) {
    echo $company->id.' '.$company->name;
}
```

## Available operations

| Operation      | Docs                                    |
| -------------- | --------------------------------------- |
| Find companies | [Finding Companies](finding-companies.md) |

## Provider support

| Operation      | Cegid Vendus | Moloni | Invoice Express |
| -------------- | ------------ | ------ | --------------- |
| Find Companies | ❌           | ✅     | ❌              |

On Cegid Vendus `Company::find()` throws `OperationNotSupportedException`: a Vendus API key belongs to a single account.
