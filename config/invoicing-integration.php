<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'cegid_vendus' => [
            'key' => env('CEGID_VENDUS_API_KEY', null),
            'mode' => env('CEGID_VENDUS_MODE', null),
            'config' => [
                'payments' => [
                    DocumentPaymentMethod::MB->value => env('CEGID_VENDUS_PAYMENT_MB_ID', null),
                    DocumentPaymentMethod::CREDIT_CARD->value => env('CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID', null),
                    DocumentPaymentMethod::CURRENT_ACCOUNT->value => env('CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID', null),
                    DocumentPaymentMethod::MONEY->value => env('CEGID_VENDUS_PAYMENT_MONEY_ID', null),
                    DocumentPaymentMethod::MONEY_TRANSFER->value => env('CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID', null),
                ],
            ],
        ],
    ],
];
