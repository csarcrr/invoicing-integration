<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with invoice notes', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'notes' => 'This is a note for the invoice.',
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['notes']);
