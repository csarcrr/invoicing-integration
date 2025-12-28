<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__ . '/Fixtures/');

dataset('create-invoice', [
    [
        function () { // resets the state for each test
            mockConfiguration(IntegrationProvider::CEGID_VENDUS);
            return CegidVendus::invoice(Action::CREATE);
        },
        fn() => Fixtures::request(IntegrationProvider::CEGID_VENDUS)
    ], 

    // [function () {
    //     mockConfiguration(IntegrationProvider::MOLONI);
    //     return Moloni::invoice(Action::CREATE);
    // }],
]);

dataset('providers', [
    [IntegrationProvider::CEGID_VENDUS],
]);

uses(TestCase::class)->in('Unit', 'Feature');

function mockConfiguration(IntegrationProvider $provider): void
{
    if ($provider === IntegrationProvider::CEGID_VENDUS) {
        config()->set('invoicing-integration.provider', IntegrationProvider::CEGID_VENDUS->value);
        config()->set('invoicing-integration.providers.' . IntegrationProvider::CEGID_VENDUS->value . '.config', [
            'payments' => [
                PaymentMethod::CREDIT_CARD->value => 1999,
                PaymentMethod::MONEY->value => 2999,
                PaymentMethod::MB->value => 3999,
                PaymentMethod::MONEY_TRANSFER->value => 4999,
                PaymentMethod::CURRENT_ACCOUNT->value => 5999,
            ],
        ]);
    }
}

function mockResponse(
    $provider,
    $type,
    $status = 200,
    $payloadOverrides = [],
    $headers = [],
) {

    $path = FIXTURES_PATH . "/Providers/{$provider->value}/Documents/{$type}.json";

    $jsonFixture = File::json($path);

    if (! empty($payloadOverrides)) {
        $jsonFixture = array_merge($jsonFixture, $payloadOverrides);
    }

    return Http::response($jsonFixture, $status, $headers);
}
