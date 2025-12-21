<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\ProviderConfig;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
    $this->provider = ProviderConfig::from(config('invoicing-integration.provider'));
});

it('can save the pdf output to storage', function () {
    Storage::fake('local');

    Http::fake(mockResponse($this->provider, 'success'));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);

    $data = Provider::resolve()->invoice()->create($this->invoice)->new();

    $path = "invoices/{$data->output()->fileName()}";

    $output = $data->output()->save($path);

    Storage::disk('local')
        ->assertExists($path);

    expect($output)->toBeString();
})->with([ProviderConfig::CegidVendus])->skipOnWindows();

it('can output escpos', function () {
    Http::fake(mockResponse(provider: $this->provider, type: 'success', payloadOverrides: [
        'output' => base64_encode('ESC_POS_EXAMPLE_STRING'),
    ]));

    $this->item->setPrice(100);
    $this->item->setReference('reference-1');

    $this->invoice->addItem($this->item);
    $this->invoice->asEscPos();

    $invoice = Provider::resolve()->invoice()->create($this->invoice)->new();

    expect($invoice->output()->get())->toBeString();
})->with([ProviderConfig::CegidVendus])->only();
