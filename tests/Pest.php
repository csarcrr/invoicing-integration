<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

uses(TestCase::class)->beforeEach(function () {
    config()->set('invoicing-integration.provider', 'cegid_vendus');
    config()->set('invoicing-integration.providers.cegid_vendus.key', '1234');
    config()->set('invoicing-integration.providers.cegid_vendus.mode', 'test');
    config()->set('invoicing-integration.providers.cegid_vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
})->group('Unit Tests')->in('Unit');

uses(TestCase::class)->beforeEach(function () {
    config()->set('invoicing-integration.provider', 'cegid_vendus');
    config()->set('invoicing-integration.providers.cegid_vendus.key', '1234');
    config()->set('invoicing-integration.providers.cegid_vendus.mode', 'test');
    config()->set('invoicing-integration.providers.cegid_vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
})->group('Feature Tests')->in('Feature');

function mockResponse(
    $provider,
    $type,
    $status = 200,
    $payloadOverrides = [],
    $headers = [],
) {

    $path = FIXTURES_PATH."/Providers/{$provider->value}/Documents/{$type}.json";

    $jsonFixture = File::json($path);

    if (! empty($payloadOverrides)) {
        $jsonFixture = array_merge($jsonFixture, $payloadOverrides);
    }

    return Http::response($jsonFixture, $status, $headers);
}
