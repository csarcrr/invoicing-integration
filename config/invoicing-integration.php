<?php

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'CegidVendus' => [
            'key' => env('CEGID_VENDUS_API_KEY', null),
            'mode' => env('CEGID_VENDUS_MODE', null),
            'config' => [
                'payments' => [
                    PaymentMethod::MB->value => env('CEGID_VENDUS_PAYMENT_MB_ID', null),
                    PaymentMethod::CREDIT_CARD->value => env('CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID', null),
                    PaymentMethod::CURRENT_ACCOUNT->value => env('CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID', null),
                    PaymentMethod::MONEY->value => env('CEGID_VENDUS_PAYMENT_MONEY_ID', null),
                    PaymentMethod::MONEY_TRANSFER->value => env('CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID', null),
                ],
            ],
        ],
    ],
];
