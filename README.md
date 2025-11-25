# This package aims to help integrations with invoicing systems

[![Latest Version on Packagist](https://img.shields.io/packagist/v/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)

## Current Features

For a full list of features and capabilities, please see [FEATURES.md](docs/features.md).

## Usage

For more in-depth usage details and examples, please visit the [official documentation](https://invoicing-integration.csarcorreia.workers.dev/).

## Installation

You can install the package via composer:

```bash
composer require csarcrr/invoicing-integration
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

This is the contents of the published config file:

```php
<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'cegid_vendus' => [
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

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [csarcrr](https://github.com/csarcrr)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
