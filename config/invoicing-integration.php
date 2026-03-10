<?php

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

return [

    /*
    |--------------------------------------------------------------------------
    | Active Provider
    |--------------------------------------------------------------------------
    |
    | The provider key used to issue invoices and manage clients. This must
    | match one of the keys defined under 'providers' below.
    |
    */

    'provider' => env('INVOICING_INTEGRATION_PROVIDER', null),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Each provider has its own configuration block keyed by its name.
    | Only the block matching the 'provider' key above will be used.
    |
    */

    'providers' => [

        'CegidVendus' => [

            /*
            |----------------------------------------------------------------------
            | API Key
            |----------------------------------------------------------------------
            |
            | Your Cegid Vendus API key. You can find it in the dashboard
            | under Definições → API.
            |
            */

            'key' => env('CEGID_VENDUS_API_KEY', null),

            /*
            |----------------------------------------------------------------------
            | Mode
            |----------------------------------------------------------------------
            |
            | Controls which Cegid Vendus environment the package connects to:
            |
            |   tests  — Sandbox mode; documents are not fiscally valid
            |             and do not require an AT registration code.
            |
            |   normal — Production mode; documents are fiscally valid
            |             and require a valid AT CUD (ATCUD hash).
            |
            */

            'mode' => env('CEGID_VENDUS_MODE', null),

            /*
            |----------------------------------------------------------------------
            | Payment Methods
            |----------------------------------------------------------------------
            |
            | Maps each PaymentMethod enum value to its numeric Cegid Vendus
            | payment method ID. Find IDs in Definições → Métodos de
            | Pagamento (check the URL on each entry).
            |
            | Note: IDs differ between 'tests' and 'normal' mode accounts.
            |
            */

            'payments' => [
                PaymentMethod::MB->value => env('CEGID_VENDUS_PAYMENT_MB_ID', null),
                PaymentMethod::CREDIT_CARD->value => env('CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID', null),
                PaymentMethod::CURRENT_ACCOUNT->value => env('CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID', null),
                PaymentMethod::MONEY->value => env('CEGID_VENDUS_PAYMENT_MONEY_ID', null),
                PaymentMethod::MONEY_TRANSFER->value => env('CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID', null),
            ],

            /*
            |----------------------------------------------------------------------
            | Units
            |----------------------------------------------------------------------
            |
            | Maps unit string values to their numeric Cegid Vendus unit IDs.
            | The keys must match the values of the Unit enum (or any custom
            | enum implementing ShouldBeUnit). Add an entry for every
            | unit you intend to use when creating catalog items.
            |
            */

            'units' => [
                'kg' => env('CEGID_VENDUS_UNIT_KG_ID', null),
                'unit' => env('CEGID_VENDUS_UNIT_UNIT_ID', null),
            ],

        ],

    ],

];
