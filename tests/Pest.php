<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

function invoice(): CreateInvoice
{
    mockConfiguration(IntegrationProvider::CEGID_VENDUS);

    return CegidVendus::invoice(Action::CREATE);
}

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
) {
    return Http::response($jsonFixture, $status, $headers);
}
