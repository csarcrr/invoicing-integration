<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

function invoice(): CreateInvoice
{
    mockConfiguration(IntegrationProvider::CEGID_VENDUS);

    return Invoice::create();
}

function client(): IntegrationProvider
{
    mockConfiguration(IntegrationProvider::CEGID_VENDUS);

    return IntegrationProvider::current();
}

dataset('client', [
    [
        fn () => client(),
    ],
]);

dataset('invoice', [
    [
        fn () => invoice(),
    ],
]);

dataset('invoice-full', [
    [
        fn () => invoice(),
        fn (): Fixtures => Fixtures::build(IntegrationProvider::CEGID_VENDUS),
    ],
]);

dataset('client-full', [
    [
        fn () => client(),
        fn (): Fixtures => Fixtures::build(IntegrationProvider::CEGID_VENDUS),
    ],
]);

dataset('providers', [
    [IntegrationProvider::CEGID_VENDUS],
]);

uses(TestCase::class)->in('Unit', 'Feature');

function mockConfiguration(IntegrationProvider $provider): void
{
    if ($provider === IntegrationProvider::CEGID_VENDUS) {
        config()->set('invoicing-integration.provider', IntegrationProvider::CEGID_VENDUS->value);
        config()->set('invoicing-integration.providers.'.IntegrationProvider::CEGID_VENDUS->value, [
            'key' => 'test-api-key',
            'mode' => 'normal',
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
    $jsonFixture,
    $status = 200,
    $headers = [],
): PromiseInterface {
    return Http::response($jsonFixture, $status, $headers);
}
