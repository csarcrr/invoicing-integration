<?php

use CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
});

it('can invoice ', function ($url, $response) {
    $response['type'] = 'FS';
    Http::fake([
        $url => Http::response($response, 200),
    ]);

    $invoice = InvoicingIntegration::create()
        ->forClient(new InvoicingClient(name: 'Client Name', vat: '123456789'))
        ->withItem(new InvoicingItem(reference: 'ref-1'))
        ->asFaturaRecibo()
        ->invoice();

    expect($invoice->sequenceNumber())->toBe('FT 2025/0001');
})->with([
    [
        'vendus.pt/ws/v1.1/documents/',
        [
            'type' => 'FT',
            'number' => 'FT 2025/0001',
        ],
    ],
]);
