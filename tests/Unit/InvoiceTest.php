<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use CsarCrr\InvoicingIntegration\InvoicingPayment;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
});

it('assigns one simple item to the invoice', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->addItem(new InvoicingItem('reference-1'));

    expect($invoice->items()->count())->toBe(1);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
});

it('assigns a client to an invoice', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->setClient(new InvoicingClient(vat: '123456789'));

    expect($invoice->client()->vat)->toBe('123456789');
});

it('assigns multiple items to the invoice', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->addItem(new InvoicingItem(reference: 'reference-1'));
    $invoice->addItem(new InvoicingItem(reference: 'reference-2'));

    expect($invoice->items()->count())->toBe(2);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
    expect($invoice->items()->last()->reference)->toBe('reference-2');
});

it('can assign all different invoice types', function ($type) {
    $type = DocumentType::from($type);
    $invoice = InvoicingIntegration::create();
    $invoice->setType($type);

    expect($invoice->type())->toBe($type);
})->with(DocumentType::options()); // when using CASES it causes clutter in the test results

it('automatically defines a date when no date is provided', function () {
    $invoice = InvoicingIntegration::create();

    expect($invoice->date())->toBeInstanceOf(Carbon::class);
});

it('can change the date', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->setDate(Carbon::now()->addDays(5));

    expect($invoice->date())->toBeInstanceOf(Carbon::class);
    expect($invoice->date()->toDateString())->toBe(Carbon::now()->addDays(5)->toDateString());
});

it('can assign a custom price to an item', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoice = InvoicingIntegration::create();
    $invoice->addItem($item);

    expect($invoice->items()->first())->toBeInstanceOf(InvoicingItem::class);
    expect($invoice->items()->first()->price())->toBe(500);
});

it('assigns a payment', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->addPayment(new InvoicingPayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));

    expect($invoice->payments()->count())->toBe(1);
    expect($invoice->payments()->first())->toBeInstanceOf(InvoicingPayment::class);
    expect($invoice->payments()->first()->method)->toBe(DocumentPaymentMethod::CREDIT_CARD);
    expect($invoice->payments()->first()->amount)->toBe(500);
});

it('fails to invoice when client has name but no vat', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->setClient(new InvoicingClient(name: 'John Doe'));
    $invoice->addItem(new InvoicingItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $invoice = InvoicingIntegration::create();
    $invoice->setClient(new InvoicingClient(vat: ''));
    $invoice->addItem(new InvoicingItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresClientVatException::class);
