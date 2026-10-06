# Finding Payment Methods

Use `PaymentMethod::find()` to list the payment methods stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

$results = PaymentMethod::find()->execute();

foreach ($results->getList() as $paymentMethod) {
    $paymentMethod->id;
    $paymentMethod->name;
    $paymentMethod->type; // PaymentMethod enum case, or null
}
```

- `getList()` returns a `Collection` of `PaymentMethodData` instances.
- `PaymentMethod::find()` takes no filters: the provider returns every payment method of the account.

## Pagination

`PaymentMethod::find()` implements `next()`, `previous()` and `page()` through the `HasPaginator` trait.

```php
$results = PaymentMethod::find()->execute();

while ($results->getList()->isNotEmpty()) {
    // do stuff

    $results->next()->execute();
}
```

- Each page holds up to 20 payment methods.
- `getTotalPages()` returns `null`, because Moloni does not report a total: keep calling `next()` until a page comes back empty.
- `previous()` before page 1, or `page()` with a value below 1, raises `NoMorePagesException`.

## Moloni

The request is sent to `paymentMethods/getAll` with `qty` and `offset`. Each row is mapped as:

| Moloni field        | `PaymentMethodData` |
| ------------------- | ------------------- |
| `payment_method_id` | `id`                |
| `name`              | `name`              |
| flags (see below)   | `type`              |

`type` is the first flag that is set, checked in this order:

| Moloni flag   | `type`                      |
| ------------- | --------------------------- |
| `is_numerary` | `PaymentMethod::MONEY`       |
| `is_mb`       | `PaymentMethod::MB`          |
| `is_credit`   | `PaymentMethod::CREDIT_CARD` |
| none          | `null`                      |

A flag that is missing from the response counts as not set. `MONEY_TRANSFER` and `CURRENT_ACCOUNT` are never returned, because Moloni has no flag for them.

## Cegid Vendus

Not supported: `PaymentMethod::find()` throws `OperationNotSupportedException`.

## Reference

See also:

- [Payment Methods Overview](README.md)
- [Creating a Payment Method](creating-a-payment-method.md)
- [API Reference – ShouldFindPaymentMethod](../api-reference.md#shouldfindpaymentmethod-contract)
