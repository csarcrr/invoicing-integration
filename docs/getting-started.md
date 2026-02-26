# Getting Started

This guide walks you through installation, configuration, and issuing your first invoice.

## 1. Install the Package

```bash
composer require csarcrr/invoicing-integration
```

## 2. Publish the Configuration

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

## 3. Configure Environment Variables

Add the following to your `.env` file:

```bash
# Select your provider
INVOICING_INTEGRATION_PROVIDER=CegidVendus

# Cegid Vendus credentials
CEGID_VENDUS_API_KEY=your-api-key
CEGID_VENDUS_MODE=tests   # "tests" issues training documents, "normal" issues fiscal documents

# Payment method IDs (required)
CEGID_VENDUS_PAYMENT_MB_ID=123456
CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID=123457
CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID=123458
CEGID_VENDUS_PAYMENT_MONEY_ID=123459
CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID=123460
```

## 4. Configuration File

Each provider has its own configuration requirements. Currently, only **Cegid Vendus** is supported. See [Cegid Vendus Configuration](providers/cegid-vendus/configuration.md) for a full understanding of the configuration options.

The published configuration file (`config/invoicing-integration.php`) looks like this:

```php
<?php

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'CegidVendus' => [
            'key' => env('CEGID_VENDUS_API_KEY'),
            'mode' => env('CEGID_VENDUS_MODE'),
            'payments' => [
                PaymentMethod::MB->value => env('CEGID_VENDUS_PAYMENT_MB_ID'),
                PaymentMethod::CREDIT_CARD->value => env('CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID'),
                PaymentMethod::CURRENT_ACCOUNT->value => env('CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID'),
                PaymentMethod::MONEY->value => env('CEGID_VENDUS_PAYMENT_MONEY_ID'),
                PaymentMethod::MONEY_TRANSFER->value => env('CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID'),
            ],
        ],
    ],
];
```

> **Note:** Payment method IDs are required. See [Cegid Vendus Configuration](providers/cegid-vendus/configuration.md) for details on obtaining these IDs.

## 5. Your First Invoice

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    'items' => [
        ItemData::make([
            'reference' => 'USB-CABLE-C',
            'note' => 'USB-C Charging Cable 2m',
            'price' => 1299,
            'quantity' => 2,
        ]),
    ],
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY,
            'amount' => 2598,
        ]),
    ],
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();
```

Sample invoice response:

```json
{
    "id": 4567,
    "sequence": "FR 01P2025/1",
    "total": 2598,
    "totalNet": 2112,
    "atcudHash": null
}
```

When you need to pass structured data (clients, payments, etc.), prefer the
`::make()` named constructor provided by `spatie/laravel-data` so every field is
validated and transformed before the request reaches the provider.

## Understanding the API

Invoice creation is DTO-first. Build an `InvoiceData` object and hand it to the
`Invoice` facade:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

$invoiceData = InvoiceData::make([
    'items' => [...],
    'payments' => [...],
    'client' => $clientData ?? null,
    'type' => InvoiceType::Invoice,
]);

$action = Invoice::create($invoiceData);
$result = $action->execute()->getInvoice();
```

Need to adjust the payload dynamically (e.g., append a payment when a queue job
resumes)? Mutate the `InvoiceData` instance directly (its properties are public,
and collections inside it are regular Laravel collections) or create a new DTO
via `InvoiceData::from([...])` before calling `Invoice::create()` again.

## Troubleshooting

### 401 Unauthorized Error

If you see an `UnauthorizedException`, check the following:

- **API Key**: Verify `CEGID_VENDUS_API_KEY` in your `.env` is correct
- **Trailing spaces**: Make sure there are no extra spaces in the API key value
- **Account status**: Confirm your Cegid Vendus account is active

### Invalid Payment Method Error

If payments fail:

- **Payment IDs**: Ensure all `CEGID_VENDUS_PAYMENT_*_ID` values are set and match the IDs from your Cegid Vendus dashboard
- **Mode mismatch**: Payment IDs are different between `tests` and `normal` mode accounts

### Provider Unreachable

If you get a `FailedReachingProviderException`:

- Check your internet connection
- Verify the Cegid Vendus API is not experiencing downtime
- Look for network/firewall issues that might block outgoing HTTPS requests

## Next Steps

Now that you've issued your first invoice, explore these common workflows:

- **[Creating an Invoice](invoices/creating-an-invoice.md)** - Full invoice with customer details, multiple items, and discounts
- **[Creating a Receipt (RG)](invoices/creating-a-RG-for-an-invoice.md)** - Issue receipts for existing invoices
- **[Credit Notes (NC)](invoices/creating-a-nc-invoice.md)** - Handle refunds and returns
- **[Managing Clients](clients/README.md)** - Register and reuse customers

---

Next: [Creating an Invoice](invoices/creating-an-invoice.md)
