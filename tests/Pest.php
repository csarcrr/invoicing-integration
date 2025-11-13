<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class)->beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
})->group('Unit Tests')->in('Unit');

uses(TestCase::class)->beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
})->group('Feature Tests')->in('Feature');

function buildFakeHttpResponses(string $integration, array $type): array
{
    $responses = [];

    foreach ($type as $t) {
        switch ($integration) {
            case 'vendus':
                $responses = array_merge($responses, vendus($t));
                break;
        }
    }

    return $responses;
}

function vendus(string $name): array
{
    $array = [
        'new_document' => [
            'https://www.vendus.pt/ws/*/documents/' => Http::response(['number' => 'FT 10000'], 200)
        ]
    ];

    return $array[$name];
}
