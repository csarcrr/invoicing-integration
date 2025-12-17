<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('can save the pdf output to storage', function () {
    Storage::fake('local');
    Http::fake(buildFakeHttpResponses(['cegid_vendus', 200], ['new_document']));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $invoice = $resolve->create()->invoice();

    $path = "invoices/{$invoice->output()->fileName()}";

    $output = $invoice->output()->save($path);

    Storage::disk('local')
        ->assertExists($path);

    expect($output)->toBeString();
})->skipOnWindows();

it('can output escpos', function () {
    Http::fake(buildFakeHttpResponses(['cegid_vendus', 200], ['new_document']));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);
    $this->invoice->asEscPos();

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $invoice = $resolve->create()->invoice();

    expect($invoice->output()->get())->toBeString();
});
