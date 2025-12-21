<?php

use CsarCrr\InvoicingIntegration\Enums\InvoicePaymentMethod;

return [
    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),
    'providers' => [
        'CegidVendus' => [
            'key' => env('CEGID_VENDUS_API_KEY', null),
            'mode' => env('CEGID_VENDUS_MODE', null),
            'config' => [
                'payments' => [
                    InvoicePaymentMethod::MB->value => env('CEGID_VENDUS_PAYMENT_MB_ID', null),
                    InvoicePaymentMethod::CREDIT_CARD->value => env('CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID', null),
                    InvoicePaymentMethod::CURRENT_ACCOUNT->value => env('CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID', null),
                    InvoicePaymentMethod::MONEY->value => env('CEGID_VENDUS_PAYMENT_MONEY_ID', null),
                    InvoicePaymentMethod::MONEY_TRANSFER->value => env('CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID', null),
                ],
            ],
        ],
    ],
];
