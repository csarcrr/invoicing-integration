<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
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

it('assigns one simple item to the invoice', function () {
    $invoice = Invoice::create();
    $invoice->addItem(new InvoicingItem('reference-1'));

    expect($invoice->items()->count())->toBe(1);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
});

it('assigns multiple items to the invoice', function () {
    $invoice = Invoice::create();
    $invoice->addItem(new InvoicingItem(reference: 'reference-1'));
    $invoice->addItem(new InvoicingItem(reference: 'reference-2'));

    expect($invoice->items()->count())->toBe(2);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
    expect($invoice->items()->last()->reference)->toBe('reference-2');
});

it('can assign a custom price to an item', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoice = Invoice::create();
    $invoice->addItem($item);

    expect($invoice->items()->first())->toBeInstanceOf(InvoicingItem::class);
    expect($invoice->items()->first()->price())->toBe(500);
});
