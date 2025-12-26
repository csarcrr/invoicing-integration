<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use CsarCrr\InvoicingIntegration\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

define('FIXTURES_PATH', __DIR__ . '/Fixtures/');

dataset('create-invoice', [
    [fn() => Create::create()] // resets the state for each test
]);

dataset('providers', [
    [IntegrationProvider::CEGID_VENDUS],
]);

uses(TestCase::class)->in('Unit', 'Feature');

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
