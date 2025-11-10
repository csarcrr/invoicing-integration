<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\InvoicingClient;
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

it('has a valid price payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

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
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});

it('has a valid type', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();

    expect($resolve->payload()->get('type'))->toBe('FT');
});

it('has a valid payment payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->payments(new InvoicingPayment(amount: 500, method: DocumentPaymentMethod::MB));

    $resolve->buildPayload();

    expect($resolve->payload()->get('payments'))->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
    expect($resolve->payload()->get('payments')->first()['id'])->toBe(19999);
});

it('does not set items when issuing a RG', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect([199999]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->isEmpty())->toBeTrue();
});

it('has a valid related documents payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect([199999, 299999]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('invoices'))->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('invoices')->first())->toBe(199999);
    expect($resolve->payload()->get('invoices')->last())->toBe(299999);
});

it('transforms incorrectly formatted related documents to integers', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocuments(collect(['199999']));

    $resolve->buildPayload();

    expect($resolve->payload()->get('invoices'))->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('invoices')->first())->toBe(199999);
});

it('fails when item format is not valid', function () {
    $item = new stdClass;

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();
})->throws(InvoiceItemIsNotValidException::class);

it('fails when no payment id is configured', function () {
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => null,
        DocumentPaymentMethod::CREDIT_CARD->value => null,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => null,
        DocumentPaymentMethod::MONEY->value => null,
        DocumentPaymentMethod::MONEY_TRANSFER->value => null,
    ]);

    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->payments(
            new InvoicingPayment(
                amount: 500,
                method: DocumentPaymentMethod::MB
            )
        );

    $resolve->buildPayload();
})->throws(
    Exception::class,
    'The provider configuration is missing payment method details.'
);
