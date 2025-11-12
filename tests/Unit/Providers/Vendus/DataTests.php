<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
});

it('clears empty data entries', function () {
    $item = new InvoicingItem(reference: 'reference-1');
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

    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->client(new InvoicingClient(vat: 'invalid-vat'));

    $resolve->buildPayload();

    $resolve->send();
})->throws(
    RequestFailedException::class,
    'A001 - Example failed message.'
);
