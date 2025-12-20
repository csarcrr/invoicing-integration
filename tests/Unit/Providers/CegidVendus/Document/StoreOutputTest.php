<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
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

it('can save the pdf output to storage', function (Provider $provider) {
    Storage::fake('local');

    Http::fake(mockResponse($provider, 'success'));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $data = $resolve->create()->invoice();

    $path = "invoices/{$data->output()->fileName()}";

    $output = $data->output()->save($path);

    Storage::disk('local')
        ->assertExists($path);

    expect($output)->toBeString();
})->with([Provider::CegidVendus])->skipOnWindows();

it('can output escpos', function (Provider $provider) {
    Http::fake(mockResponse($provider, 'success'));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);
    $this->invoice->asEscPos();

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $invoice = $resolve->create()->invoice();

    expect($invoice->output()->get())->toBeString();
})->with([Provider::CegidVendus]);
