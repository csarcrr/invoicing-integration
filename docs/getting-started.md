# Getting Started

This guide will help you install and configure the Invoicing Integration package in your Laravel project.

## 1. Install the Package

Run the following command in your Laravel project:

```bash
composer require csarcrr/invoicing-integration
```

## 2. Publish the Configuration

Publish the configuration file with:

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

## 3. Configure the Provider

The default configuration will look like this:

```php
<?php
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'Cegid Vendus' => [
            'key' => env('VENDUS_API_KEY', null),
            'mode' => env('VENDUS_MODE', null),
            'config' => [
                'payments' => [
                    DocumentPaymentMethod::MB->value => env('VENDUS_PAYMENT_MB_ID', null),
                    DocumentPaymentMethod::CREDIT_CARD->value => env('VENDUS_PAYMENT_CREDIT_CARD_ID', null),
                    DocumentPaymentMethod::CURRENT_ACCOUNT->value => env('VENDUS_PAYMENT_CURRENT_ACCOUNT_ID', null),
                    DocumentPaymentMethod::MONEY->value => env('VENDUS_PAYMENT_MONEY_ID', null),
                    DocumentPaymentMethod::MONEY_TRANSFER->value => env('VENDUS_PAYMENT_MONEY_TRANSFER_ID', null),
                ]
            ]
        ],
    ],
];
```

> **Note:** Currently, only Cegid Vendus is supported. For more details on provider configuration, see [Cegid Vendus Configuration](providers/cegid-vendus/configuration.md).

---

Next: [Creating an Invoice](invoices/creating-an-invoice.md)
