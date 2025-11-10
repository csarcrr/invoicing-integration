<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;

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

it('has a valid client payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $client = new InvoicingClient(vat: '123456789', name: 'Client Name');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->client($client)
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});
