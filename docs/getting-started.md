# How to get started

In your Laravel project run

```bash
composer require csarcrr/invoicing-integration
```

After the following to publish the config

```bash
php artisan vendor:publish --tag="invoicing-integration-config"
```

The config will currently look like this

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

Since we currently only support Cegid Vendus, you should check out the <a href="/providers/Cegid Vendus/configuration/">Cegid Vendus Config</a> section to learn more about these configurations.
