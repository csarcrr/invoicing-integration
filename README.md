# Invoicing Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)

Invoicing Integration is an aggregator for invoicing software providers in Portugal. It provides a unified, fluent API so you can issue invoices without learning the nuances of each provider's API.

**Supported Providers:** Cegid Vendus

## Important Legal Disclaimer

**This package facilitates invoicing provider API usage and is not intended to serve as legal guidance.**

It is **your responsibility** to:

- Comply with all applicable invoicing laws and regulations in your jurisdiction
- Understand each provider's specific requirements
- Ensure proper invoicing practices according to your legal obligations
- Validate that your usage complies with tax laws and accounting standards

Always consult with legal and accounting professionals when implementing invoicing solutions.

## Installation

```bash
composer require csarcrr/invoicing-integration
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

## Configuration

Set your provider and credentials in your `.env` file:

```bash
INVOICING_INTEGRATION_PROVIDER=CegidVendus

# Cegid Vendus credentials
CEGID_VENDUS_API_KEY=your-api-key
CEGID_VENDUS_MODE=tests   # "tests" issues training documents, "normal" issues fiscal documents

# Payment method IDs (required - get these from your Cegid Vendus account)
CEGID_VENDUS_PAYMENT_MB_ID=123456
CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID=123457
CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID=123458
CEGID_VENDUS_PAYMENT_MONEY_ID=123459
CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID=123460
```

See [Cegid Vendus Configuration](docs/providers/cegid-vendus/configuration.md) for details on obtaining payment IDs.

## Quick Start

Issuing a simple FT invoice.

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

$item = new Item();
$item->reference('SKU-001');

$invoice = Invoice::create();
$invoice->item($item);

$result = $invoice->invoice();

echo $result->getSequence();  // e.g., "FT 01P2025/1"

// Save the PDF (when available)
$path = $result->getOutput()->save('invoices/' . $result->getOutput()->fileName());
```

## Configuration File

The published `config/invoicing-integration.php` file exposes provider credentials and payment
mapping:

```php
<?php

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER'),

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

- `mode` must be either `normal` (fiscal documents) or `tests` (training mode)
- Each payment method maps to the numeric ID you obtain from Cegid Vendus

## Features

For a full list of features and provider compatibility, see [FEATURES.md](docs/features.md).

## Documentation

For detailed usage and examples, visit the [official documentation](https://csarcrr.github.io/invoicing-integration/#/).

- [Getting Started](docs/getting-started.md)
- [Creating an Invoice](docs/invoices/creating-an-invoice.md)
- [Creating a Receipt (RG)](docs/invoices/creating-a-RG-for-an-invoice.md)
- [Creating a Credit Note (NC)](docs/invoices/creating-a-nc-invoice.md)
- [Output Formats](docs/invoices/outputting-invoice.md)
- [API Reference](docs/api-reference.md)

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [csarcrr](https://github.com/csarcrr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
