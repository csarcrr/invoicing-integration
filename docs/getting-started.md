# Getting Started

This guide will help you install and configure the Invoicing Integration package in your Laravel project.

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
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Create a simple invoice (final consumer, no client details)
$invoice = Invoice::create();

// Add an item (price in cents)
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000,
    'quantity' => 1,
]);
$invoice->item($item);

// Issue the invoice
$result = $invoice->execute();

// Get the invoice sequence number
echo $result->sequence; // e.g., "FT 01P2025/1"
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
    ->client($client)      // Optional: client details
    ->item($item)          // Required: at least one item
    ->payment($payment)    // Required for FR, FS, RG, NC types
    ->type($invoiceType)   // Optional: defaults to FT
    ->notes('...')         // Optional: invoice notes
    ->dueDate($date);      // Optional: only for FT type

// Issue the invoice
$result = $invoice->execute();
```

---

Next: [Creating an Invoice](invoices/creating-an-invoice.md)
