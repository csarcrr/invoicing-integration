<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('issues the document in the configured mode', function (Provider $provider, string $mode) {
    config()->set('invoicing-integration.providers.'.$provider->value.'.mode', $mode);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('mode'))->toBe($mode),
        Provider::MOLONI => expect($payload->has('mode'))->toBeFalse(),
    };
})->with('providers')->with(['tests', 'normal']);

it('does not send a mode when none is configured', function (Provider $provider) {
    config()->set('invoicing-integration.providers.'.$provider->value.'.mode', null);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    expect($payload->has('mode'))->toBeFalse();
})->with('providers');
