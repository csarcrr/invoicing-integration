# Creating a Payment Method

Use `PaymentMethod::create()` to register a payment method in the provider.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

$paymentMethod = PaymentMethodData::make([
    'name' => 'Multibanco',
    'type' => PaymentMethodEnum::MB,
]);

$created = PaymentMethod::create($paymentMethod)->execute()->getPaymentMethod();

echo $created->id; // provider-assigned ID
```

## Available fields

| Field  | Type             | Default | Description                               |
| ------ | ---------------- | ------- | ----------------------------------------- |
| `name` | `string`         | —       | Required. Name of the payment method      |
| `type` | `?PaymentMethod` | `null`  | Kind of payment method                    |
| `id`   | `?int`           | `null`  | Provider-assigned ID, filled after create |

`PaymentMethodData::make()` throws a `ValidationException` when `name` is missing.

## Moloni

The request is sent to `paymentMethods/insert` with `name` and three flags. The flags come from `type`:

| `type`                                      | `is_numerary` | `is_mb` | `is_credit` |
| ------------------------------------------- | ------------- | ------- | ----------- |
| `MONEY`                                     | `1`           | `0`     | `0`         |
| `MB`                                        | `0`           | `1`     | `0`         |
| `CREDIT_CARD`                               | `0`           | `0`     | `1`         |
| `MONEY_TRANSFER`, `CURRENT_ACCOUNT`, `null` | `0`           | `0`     | `0`         |

Moloni has no flag for `MONEY_TRANSFER` or `CURRENT_ACCOUNT`, so a method created with one of them comes back from `PaymentMethod::find()` with `type` set to `null`.

The returned `payment_method_id` is set as `id`. Other response fields are available through `getAdditionalData()`.

## Cegid Vendus

Not supported: `PaymentMethod::create()` throws `OperationNotSupportedException`.

## Error handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $created = PaymentMethod::create($paymentMethod)->execute()->getPaymentMethod();
} catch (RequestFailedException $e) {
    Log::error('Payment method creation failed', ['message' => $e->getMessage()]);
}
```

For a full list of exceptions, see [Handling Errors](../handling-errors.md).
