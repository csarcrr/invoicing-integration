# Invoicing Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)

Invoicing Integration is a Laravel package that aggregates invoicing software providers in Portugal. It offers a fluent, provider-agnostic API so you can issue compliant documents without re-learning each vendor's HTTP contract.

> **Supported provider (today):** Cegid Vendus. The package architecture allows more providers to be added without changing your application code.

## Highlights

- Fluent builders for all fiscal document types (FT, FR, FS, RG, NC, GT)
- First-class client management via the `Client` facade (create, get, find)
- Strongly typed DTOs powered by `spatie/laravel-data`
- Built-in PDF / ESC/POS outputs with secure file persistence helpers
- Centralized HTTP error handling and provider configuration facades

## Table of Contents

- [Important Legal Disclaimer](#important-legal-disclaimer)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Quick Start](#quick-start)
- [Error Handling](#error-handling)
- [Testing & Quality](#testing--quality)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Important Legal Disclaimer

**This package facilitates invoicing provider API usage and is not intended to serve as legal guidance.**

It is **your responsibility** to:

- Comply with all invoicing laws and regulations in your jurisdiction
- Understand each provider's specific requirements
- Ensure proper invoicing practices according to your legal obligations
- Validate that your usage complies with tax laws and accounting standards

Always consult with legal and accounting professionals when implementing invoicing solutions.

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x (`illuminate/contracts: ^11.0 || ^12.0`)

## Installation

```bash
composer require csarcrr/invoicing-integration
```

Publish the configuration file once the package is installed:

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

## Configuration

Set your provider and credentials in `.env`:

```bash
INVOICING_INTEGRATION_PROVIDER=CegidVendus

# Cegid Vendus credentials
CEGID_VENDUS_API_KEY=your-api-key
CEGID_VENDUS_MODE=tests   # "tests" issues training documents, "normal" issues fiscal documents

# Payment method IDs (from Cegid Vendus UI)
CEGID_VENDUS_PAYMENT_MB_ID=123456
CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID=123457
CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID=123458
CEGID_VENDUS_PAYMENT_MONEY_ID=123459
CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID=123460
```

`config/invoicing-integration.php` mirrors those values:

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

- `mode` accepts `normal` (fiscal documents) or `tests` (training mode)
- Payment IDs must match the numeric identifiers you copy from the Cegid Vendus UI ([guide](docs/providers/cegid-vendus/configuration.md))

### Runtime access to provider configuration

Use the `ProviderConfiguration` facade when you need to inspect or cache the active provider during runtime:

```php
use CsarCrr\InvoicingIntegration\Facades\ProviderConfiguration;

$activeProvider = ProviderConfiguration::getProvider();
$config = ProviderConfiguration::getConfig();
```

### Environment reference

| Variable                         | Description                                                  |
| -------------------------------- | ------------------------------------------------------------ |
| `INVOICING_INTEGRATION_PROVIDER` | Provider key (`CegidVendus` for now)                         |
| `CEGID_VENDUS_API_KEY`           | API token generated in Vendus                                |
| `CEGID_VENDUS_MODE`              | `tests` (sandbox) or `normal` (fiscal)                       |
| `CEGID_VENDUS_PAYMENT_*`         | Mapping between `PaymentMethod` enums and Vendus payment IDs |

> The package validates configuration lazily when you issue invoices or clients, so misconfigured payment IDs will raise descriptive exceptions before the HTTP call is sent.

## Usage

### Issue your first invoice

Issue an FT invoice with one item and a cash payment:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create()
    ->type(InvoiceType::Invoice);

$item = ItemData::make([
    'reference' => 'SKU-001',
    'note' => 'Consulting hours',
    'price' => 10000, // cents (100.00 €)
    'quantity' => 1,
]);

$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 10000,
]);

$client = ClientData::make([
    'name' => 'John Doe',
    'vat' => 'PT123456789',
    'email' => 'john@example.com',
]);

$result = $invoice
    ->client($client)
    ->item($item)
    ->payment($payment)
    ->execute();

if ($result->output) {
    $result->output->save('invoices/' . $result->output->fileName());
}
```

> `ClientData`, `ItemData`, `PaymentData`, and other value objects extend `spatie/laravel-data\Data`. Instantiate them with `::make([...])` (or via dependency injection) so validation and transformers run before each HTTP request.

> Prefer the `Invoice` facade for day-to-day usage. If you need to resolve the underlying action for dependency injection (e.g., in jobs), bind `CsarCrr\InvoicingIntegration\InvoiceAction` from the container.

**Key rules:**

- At least one item is required for FT/FR/FS/NC documents
- Payments are required for FR, FS, RG, and NC types
- Tax exemptions require `ItemTax::EXEMPT` plus a valid `TaxExemptionReason`

### Manage clients via the facade

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

// Create a provider-side client record
$client = Client::create(
    ClientData::make([
        'name' => 'Acme Lda',
        'vat' => 'PT123456789',
        'email' => 'billing@acme.test',
    ])
)->execute();

// Retrieve it later
$fetched = Client::get(ClientData::make(['id' => $client->id]))->execute();

// Search with pagination helpers
$results = Client::find()->email('acme.test')->execute();
```

See [docs/clients/README.md](docs/clients/README.md) for pagination and filtering options.

> All DTOs expose public typed properties. Access values via `$client->name`,
> `$invoice->sequence`, etc.—legacy getter methods no longer exist.

### Common validation rules

- Receipts (RG) are the only document without items
- Credit notes require related document references, payments, and a reason
- Transport details require a client, origin date, and valid ISO 3166-1 codes
- Output formats (`PDF_BASE64`, `ESCPOS`) can be persisted via the `Output` value object (`save()`, `getPath()`, etc.)

## Quick Start

The snippet above covers the most common scenario. For detailed workflows per document type, visit:

- [Creating an Invoice](docs/invoices/creating-an-invoice.md)
- [Creating a Receipt (RG)](docs/invoices/creating-a-RG-for-an-invoice.md)
- [Credit Notes (NC)](docs/invoices/creating-a-nc-invoice.md)
- [Outputting documents (PDF/ESC-POS)](docs/invoices/outputting-invoice.md)

## Error Handling

The `Http::handleUnwantedFailures()` macro maps HTTP status codes to package-specific exceptions:

- `UnauthorizedException` → invalid or missing credentials (401)
- `FailedReachingProviderException` → provider error/unreachable (500)
- `RequestFailedException` → provider returned structured errors

Refer to [docs/handling-errors.md](docs/handling-errors.md) for the full exception matrix and troubleshooting steps.

## Testing & Quality

```bash
composer test        # Pest (parallel) test suite
composer analyse     # PHPStan (Larastan) analysis
composer format      # Laravel Pint code style
composer complete    # Format + analyse + test
```

## Documentation

Browse the full documentation at [csarcrr.github.io/invoicing-integration](https://csarcrr.github.io/invoicing-integration/#/).

- [Features Matrix](docs/features.md)
- [API Reference](docs/api-reference.md)
- [Clients](docs/clients/README.md)
- [Provider Guides](docs/providers/README.md)

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for coding standards, branching strategy, and release process.

## Security

Please review [the security policy](../../security/policy) to learn how to report vulnerabilities.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for the full text.

---

_Last updated: February 2026_
