<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Collection;

it('does not set items when issuing a RG', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment(DocumentPaymentMethod::MONEY, 500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(DocumentType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items'))->toBeNull();
});

it('has a valid related documents payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(DocumentType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');
    $invoicing->addRelatedDocument('FT 20000');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

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
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(DocumentType::Receipt);
    $invoicing->addRelatedDocument('FT 1000');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('invoices'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('invoices')->first())
        ->toBeInstanceOf(Collection::class);

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 1000');
});

it('makes sure it fails when no payments are set', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setType(DocumentType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(MissingPaymentWhenIssuingReceiptException::class);
