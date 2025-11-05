<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
});

it('has a valid price payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Fatura);

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['gross_price'])->toBe(5.0);
});

it('has a valid client payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $client = new InvoicingClient(vat: '123456789', name: 'Client Name');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->client($client)
        ->type(DocumentType::Fatura);

    $resolve->buildPayload();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});

it('has a valid type', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Fatura);

    $resolve->buildPayload();

    expect($resolve->payload()->get('type'))->toBe('FT');
});

it('fails when item format is not valid', function () {
    $item = new stdClass;

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Fatura);

    $resolve->buildPayload();
})->throws(InvoiceItemIsNotValidException::class);;
