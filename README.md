# This package aims to help integrations with invoicing systems

[![Latest Version on Packagist](https://img.shields.io/packagist/v/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/csarcrr/invoicing-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/csarcrr/invoicing-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/csarcrr/invoicing-integration.svg?style=flat-square)](https://packagist.org/packages/csarcrr/invoicing-integration)

## About

Invoicing Integration is an agregator of several invoicing softwares in Portugal. We aim to help you use these softwares
without the assle of learning every nuance of their API.

## ⚠️ Important Legal Disclaimer

**This package is a tool for facilitating the usage of invoicing provider APIs and is not intended to serve as a safeguard or provide legal guidance.**

It is the **responsibility of the end user** to:
- Understand and comply with all applicable laws and regulations regarding invoicing in their jurisdiction
- Understand how each provider works and their specific requirements
- Ensure proper invoicing practices according to their legal obligations
- Validate that their usage complies with tax laws, accounting standards, and other relevant regulations

This package aims to simplify API integration but does not provide opinions on how you should be issuing invoices. Always consult with legal and accounting professionals when implementing invoicing solutions.

## Current Features

For a full list of features and capabilities, please see [FEATURES.md](docs/features.md).

## Usage

For more in-depth usage details and examples, please visit the [official documentation](https://csarcrr.github.io/invoicing-integration/#/).

## Installation

You can install the package via composer:

```bash
composer require csarcrr/invoicing-integration
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

Example

```php
<?php

use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

$integration = Invoice::create();

$item = new Item(reference: 'SKU-001', quantity: 2);
$item->setPrice(1000);
$item->setDescription('Product Description');
$integration->addItem($item);

$invoiceData = $integration->invoice();
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
