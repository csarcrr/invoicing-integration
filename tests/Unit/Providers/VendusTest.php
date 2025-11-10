<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use CsarCrr\InvoicingIntegration\InvoicingPayment;
use Illuminate\Support\Collection;
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

it('has a valid price payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['gross_price'])
        ->toBe(5.0);
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
        ->payments(
            new InvoicingPayment(
                amount: 500,
                method: DocumentPaymentMethod::MB
            )
        );

    $resolve->buildPayload();

    expect($resolve->payload()->get('payments'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
    expect($resolve->payload()->get('payments')->first()['id'])->toBe(19999);
});

it('does not set items when issuing a RG', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocument(199999);

    $resolve->buildPayload();

    expect($resolve->payload()->get('items'))->toBeNull();
});

it('has a valid related documents payload', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Receipt)
        ->relatedDocument(199999);

    $resolve->buildPayload();

    expect($resolve->payload()->get('invoices'))->toBeArray();
    expect($resolve->payload()->get('invoices')['document_number'])->toBe(199999);
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
