# Payment Methods

A payment method is an account-level record stored in the provider (for example "Multibanco" or "Numerário"). Each one has a provider-assigned ID, which is the ID an invoice payment refers to.

> [!NOTE]
> Three things share the name:
>
> - the `PaymentMethod` **facade** and `PaymentMethodData` manage the records stored in the provider (this section);
> - the `PaymentMethod` **enum** is the fixed list of kinds of payment (`MONEY`, `MB`, ...);
> - `PaymentData` is one payment on an invoice.
>
> When a file needs both the facade and the enum, import one with an alias.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

$paymentMethod = PaymentMethod::create(PaymentMethodData::make([
    'name' => 'Multibanco',
    'type' => PaymentMethodEnum::MB,
]))->execute()->getPaymentMethod();

echo $paymentMethod->id; // provider-assigned ID
```

## Available operations

| Operation               | Docs                                                      |
| ----------------------- | --------------------------------------------------------- |
| Create a payment method | [Creating a Payment Method](creating-a-payment-method.md) |
| Find payment methods    | [Finding Payment Methods](finding-payment-methods.md)     |

## Provider support

| Operation             | Cegid Vendus | Moloni | Invoice Express |
| --------------------- | ------------ | ------ | --------------- |
| Create Payment Method | ❌           | ✅     | ❌              |
| Find Payment Methods  | ❌           | ✅     | ❌              |

On Cegid Vendus both operations throw `OperationNotSupportedException`.

On Moloni, invoice payments use these records: each payment is sent with the ID of the first account payment method whose `type` matches its `method`. A payment with no matching record throws `CouldNotGetPaymentMethodIdException`.
