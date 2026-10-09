<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\ProviderConfiguration;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

function fixtures(): Fixtures
{
    return Fixtures::build(ProviderConfiguration::getProvider());
}

dataset('providers', [
    'vendus' => fn () => cegidVendusProvider(),
    'moloni' => fn () => moloniProvider(),
]);

function cegidVendusProvider(): Provider
{
    mockConfiguration(Provider::CEGID_VENDUS);

    return ProviderConfiguration::getProvider();
}

function moloniProvider(): Provider
{
    mockConfiguration(Provider::MOLONI);

    return ProviderConfiguration::getProvider();
}

uses(TestCase::class)->in('Unit', 'Feature');

function mockConfiguration(Provider $provider): void
{
    if ($provider === Provider::MOLONI) {
        config()->set('invoicing-integration.provider', $provider->value);
        config()->set('invoicing-integration.providers.'.$provider->value, [
            'developer_id' => 'test-developer-id',
            'client_secret' => 'test-client-secret',
            'username' => 'test-username',
            'password' => 'test-password',
            'company_id' => 123456,
            'no_vat_client_id' => 999999,
            'units' => [
                'kg' => 19999,
                'unit' => 29999,
            ],
            'taxes' => [
                ItemTax::NORMAL->value => ['id' => 1, 'rate' => 23],
                ItemTax::INTERMEDIATE->value => ['id' => 2, 'rate' => 13],
                ItemTax::REDUCED->value => ['id' => 3, 'rate' => 6],
                ItemTax::OTHER->value => ['id' => 4, 'rate' => 0],
            ],
        ]);

        Http::fake([
            'api.moloni.pt/v1/grant/*' => mockResponse([
                'access_token' => 'fresh-access-token',
                'expires_in' => 3600,
                'token_type' => 'bearer',
                'scope' => null,
                'refresh_token' => 'test-refresh-token',
            ]),
        ]);

        Cache::put('invoicing_integration_moloni_access_token', [
            'access_token' => 'fresh-access-token',
        ]);
    }

    if ($provider === Provider::CEGID_VENDUS) {
        config()->set('invoicing-integration.provider', $provider->value);
        config()->set('invoicing-integration.providers.'.$provider->value, [
            'key' => 'test-api-key',
            'mode' => 'normal',
            'payments' => [
                PaymentMethod::CREDIT_CARD->value => 1999,
                PaymentMethod::MONEY->value => 2999,
                PaymentMethod::MB->value => 3999,
                PaymentMethod::MONEY_TRANSFER->value => 4999,
                PaymentMethod::CURRENT_ACCOUNT->value => 5999,
            ],
            'units' => [
                'kg' => 19999,
                'unit' => 29999,
            ],
        ]);
    }
}

function fakeProviderPaymentMethods(Provider $provider): void
{
    if ($provider !== Provider::MOLONI) {
        return;
    }

    Http::fake([
        '*paymentMethods/getAll*' => mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')),
    ]);
}

function mockResponse(
    $jsonFixture,
    $status = 200,
    $headers = [],
): PromiseInterface {
    return Http::response($jsonFixture, $status, $headers);
}
