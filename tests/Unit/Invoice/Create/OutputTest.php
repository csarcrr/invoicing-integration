<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('has pdf has the default output', function (Provider $provider) {
    $invoice = Invoice::create();

    expect($invoice->getOutputFormat())->toBe(OutputFormat::PDF_BASE64);
})->with('providers');

it('transforms to provider payload with pdf output format', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'item-1']));
    $invoice->outputFormat(OutputFormat::PDF_BASE64);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_pdf']);

it('transforms to provider payload with escpos output format', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'item-1']));
    $invoice->outputFormat(OutputFormat::ESCPOS);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_escpos']);
