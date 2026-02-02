<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('applies a note to the invoice', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->notes('This is a note for the invoice.');
    $invoice->item(ItemData::from(['reference' => 'reference-1']));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['notes']);
