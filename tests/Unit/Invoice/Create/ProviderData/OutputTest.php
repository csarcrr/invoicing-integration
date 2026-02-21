<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('has pdf has the default output', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([]));

    expect($invoice->getOutputFormat())->toBe(OutputFormat::PDF_BASE64);
})->with('providers');

it('transforms to provider payload with pdf output format', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'item-1'])],
        'output' => OutputData::make(['format' => OutputFormat::PDF_BASE64])
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_pdf']);

it('transforms to provider payload with escpos output format', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'item-1'])],
        'output' => OutputData::make(['format' => OutputFormat::ESCPOS])
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_escpos']);
