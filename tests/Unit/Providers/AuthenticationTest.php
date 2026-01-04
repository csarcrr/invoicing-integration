<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\Request;

it('properly sets the auth for ' . IntegrationProvider::CEGID_VENDUS->value, function () {
    mockConfiguration(IntegrationProvider::CEGID_VENDUS);
    $config = collect(config('invoicing-integration.providers')[IntegrationProvider::CEGID_VENDUS->value]);
    $request = Request::get(
        IntegrationProvider::CEGID_VENDUS,
        $config
    );

    $headers = $request->getOptions()['headers'];

    expect($headers)->toHaveKey('Authorization');
    expect($headers['Authorization'])->toBe('Bearer ' . $config->get('key'));
})->with('providers');
