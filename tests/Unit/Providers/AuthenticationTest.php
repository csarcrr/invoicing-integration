<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use Illuminate\Support\Facades\Http;

it('properly sets the auth for '.IntegrationProvider::CEGID_VENDUS->value, function () {
    mockConfiguration(IntegrationProvider::CEGID_VENDUS);
    $config = collect(config('invoicing-integration.providers')[IntegrationProvider::CEGID_VENDUS->value]);
    $request = Http::provider();

    $headers = $request->getOptions()['headers'];

    expect($headers)->toHaveKey('Authorization');
    expect($headers['Authorization'])->toBe('Bearer '.$config->get('key'));
})->with('providers');
