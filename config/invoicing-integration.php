<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\InvoicingPayment;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'vendus' => [
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
