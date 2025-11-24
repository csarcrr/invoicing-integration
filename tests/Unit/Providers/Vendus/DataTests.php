<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use Illuminate\Support\Facades\Http;

it('clears empty data entries', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();

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

    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->client(new InvoiceClient(vat: 'invalid-vat'));

    $resolve->buildPayload();

    $resolve->send();
})->throws(
    RequestFailedException::class,
    'A001 - Example failed message.'
)->note('this should be a feature test');
