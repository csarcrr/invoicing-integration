<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('clears empty data entries', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setType(DocumentType::Invoice);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('payments'))->toBeNull();
    expect($resolve->payload()->get('invoices'))->toBeNull();
    expect($resolve->payload()->get('register_id'))->toBeNull();
});

it('throw error exception with API messages when request fails', function () {
    Http::fake([
        'https://www.vendus.pt/ws/v1.1/documents/' => Http::response([
            'errors' => [
                ['code' => 'A001', 'message' => 'Example failed message.'],
                ['code' => 'A002', 'message' => 'Another failed message.'],
            ],
        ], 400),
    ]);

    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->client->setVat('invalid-vat');

    $this->invoice->addItem($this->item);
    $this->invoice->setType(DocumentType::Invoice);
    $this->invoice->setClient($this->client);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);
})->throws(
    RequestFailedException::class,
    'A001 - Example failed message.'
)->note('this should be a feature test');
