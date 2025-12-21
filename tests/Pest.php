<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoicePaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__.'/Fixtures/');

pest()->beforeEach(function () {
    config()->set('invoicing-integration.provider', 'CegidVendus');
    config()->set('invoicing-integration.providers.CegidVendus.key', '1234');
    config()->set('invoicing-integration.providers.CegidVendus.mode', 'test');
    config()->set('invoicing-integration.providers.CegidVendus.config.payments', [
        InvoicePaymentMethod::MB->value => 19999,
        InvoicePaymentMethod::CREDIT_CARD->value => 29999,
        InvoicePaymentMethod::CURRENT_ACCOUNT->value => 39999,
        InvoicePaymentMethod::MONEY->value => 49999,
        InvoicePaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
})->in('Unit/Providers/CegidVendus/', 'Feature/Providers/CegidVendus/');

uses(TestCase::class)->in('Unit', 'Feature');

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
