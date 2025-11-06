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
