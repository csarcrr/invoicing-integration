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
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Create a simple invoice (final consumer, no client details needed)
$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

// Add the product
$item = ItemData::make([
    'reference' => 'USB-CABLE-C',
    'note' => 'USB-C Charging Cable 2m',
    'price' => 1299, // 12.99 in cents
    'quantity' => 2,
]);
$invoice->item($item);

// Add the payment
$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 2598, // Total: 25.98
]);
$invoice->payment($payment);

// Issue the invoice
$result = $invoice->execute()->getInvoice();
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

The package uses a fluent builder pattern. All invoice operations start with the
`Invoice` facade:

```php
$invoice = Invoice::create();

// Chain methods to configure the invoice
$invoice
    ->client($client)      // Optional: customer billing details
    ->item($item)          // Required: at least one product or service
    ->payment($payment)    // Required for FR, FS, RG, NC types
    ->type($invoiceType)   // Optional: defaults to FT
    ->notes('...')         // Optional: internal notes
    ->dueDate($date);      // Optional: payment due date (FT only)

// Issue the invoice
$result = $invoice->execute()->getInvoice();
```

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
