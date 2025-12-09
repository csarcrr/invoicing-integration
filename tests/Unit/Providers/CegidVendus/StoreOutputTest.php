<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

it('can save the pdf output to storage', function () {
    Storage::fake('local');
    Http::fake(buildFakeHttpResponses(['cegid_vendus', 200], ['new_document']));

    $item = new InvoiceItem();
    $item->setPrice(100);
    $item->setReference('reference-1');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $invoice = $resolve->create()->invoice();

    $output = $invoice->output()->save();

    Storage::disk('local')
        ->assertExists(storage_path($invoice->output()->fileName()));

    expect($output)->toBeString();
})->skipOnWindows();

it('can output escpos', function () {
    Http::fake(buildFakeHttpResponses(['cegid_vendus', 200], ['new_document']));

    $item = new InvoiceItem();
    $item->setPrice(100);
    $item->setReference('reference-1');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->asEscPos();

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $invoice = $resolve->create()->invoice();

    expect($invoice->output()->get())->toBeString();
});
