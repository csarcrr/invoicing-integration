<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceItem;

it('assigns one simple item to the invoice', function () {
    $invoice = Invoice::create();
    $invoice->addItem(new InvoiceItem('reference-1'));

    expect($invoice->items()->count())->toBe(1);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
});

it('assigns multiple items to the invoice', function () {
    $invoice = Invoice::create();
    $invoice->addItem(new InvoiceItem(reference: 'reference-1'));
    $invoice->addItem(new InvoiceItem(reference: 'reference-2'));

    expect($invoice->items()->count())->toBe(2);
    expect($invoice->items()->first()->reference)->toBe('reference-1');
    expect($invoice->items()->last()->reference)->toBe('reference-2');
});

it('can assign a custom price to an item', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoice = Invoice::create();
    $invoice->addItem($item);

    expect($invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($invoice->items()->first()->price())->toBe(500);
});

it('can assign a lot number to be used', function () {})->todo();
