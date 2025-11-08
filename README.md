# This package aims to help integrations with invoicing systems

[![Latest Version on Packagist](https://img.shields.io/packagist/v/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)

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

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'vendus' => [
            'key' => env('VENDUS_API_KEY', null),
            'mode' => env('VENDUS_MODE', null),
        ],
    ],
];
```

## Usage

```php
<?php

$integration = InvoicingIntegration::create();

$integration->addItem((new InvoicingItem(reference: '31054308', quantity: 1)));
$integration->addItem(new InvoicingItem(reference: '09818943', quantity: 5));

// Optionally you can set a client
$integration->setClient(new InvoicingClient(vat: '245824820'));

$invoice = $integration->invoice();

$invoice->sequenceNumber(); // FT 01P2025/001
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [csarcrr](https://github.com/csarcrr)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
