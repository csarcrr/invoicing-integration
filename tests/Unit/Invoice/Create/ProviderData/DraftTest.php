<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('issues the final document by default', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->has('status'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('status'))->toBe(1),
    };
})->with('providers');

it('can create the document as a draft', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->draft()->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->has('status'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('status'))->toBe(0),
    };
})->with('providers');
