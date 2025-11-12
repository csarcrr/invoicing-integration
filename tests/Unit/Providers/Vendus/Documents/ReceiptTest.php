<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use CsarCrr\InvoicingIntegration\InvoicingPayment;
use Illuminate\Support\Collection;

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

it('does not set items when issuing a RG', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicingPayment(DocumentPaymentMethod::MONEY, 500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect(['FT 10000']))
        ->payments(collect([$payment]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items'))->toBeNull();
});

it('has a valid related documents payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicingPayment(amount: 500, method: DocumentPaymentMethod::MB);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect(['FT 10000', 'FT 20000']))
        ->payments(collect([$payment]));

    $resolve->buildPayload();

    expect(
        $resolve->payload()->get('invoices')
    )->toBeInstanceOf(Collection::class);

    expect(
        $resolve->payload()->get('invoices')->first()->get('document_number')
    )->toBe('FT 10000');

    expect(
        $resolve->payload()->get('invoices')->last()->get('document_number')
    )->toBe('FT 20000');
});

it('makes sure that invoices document numbers are string', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicingPayment(amount: 500, method: DocumentPaymentMethod::MB);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect(['FT 1000']))
        ->payments(collect([$payment]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('invoices'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('invoices')->first())
        ->toBeInstanceOf(Collection::class);

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 1000');
});

it('makes sure it fails when no payments are set', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect(['FT 10000']));

    $resolve->buildPayload();
})->throws(MissingPaymentWhenIssuingReceiptException::class);
