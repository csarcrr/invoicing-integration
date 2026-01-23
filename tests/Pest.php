<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

function fixtures(): Fixtures
{
    return Fixtures::build(Provider::current());
}
function invoice(): CreateInvoice
{
    mockConfiguration(Provider::CEGID_VENDUS);

    return Invoice::create();
}

function client(): Provider
{
    mockConfiguration(Provider::CEGID_VENDUS);

    return Provider::current();
}

dataset('providers', [
    'vendus' => fn () => cegidVendusProvider(),
]);

function cegidVendusProvider(): Provider
{
    mockConfiguration(Provider::CEGID_VENDUS);

    return Provider::current();
}

uses(TestCase::class)->in('Unit', 'Feature');

function mockConfiguration(Provider $provider): void
{
    if ($provider === Provider::CEGID_VENDUS) {
        config()->set('invoicing-integration.provider', Provider::CEGID_VENDUS->value);
        config()->set('invoicing-integration.providers.'.Provider::CEGID_VENDUS->value, [
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
    $jsonFixture,
    $status = 200,
    $headers = [],
): PromiseInterface {
    return Http::response($jsonFixture, $status, $headers);
}
