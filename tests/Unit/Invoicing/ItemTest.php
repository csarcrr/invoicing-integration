<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('assigns one simple item to the invoice', function () {
    $this->invoice->addItem(new InvoiceItem('reference-1'));

    expect($this->invoice->items()->count())->toBe(1);
    expect($this->invoice->items()->first()->reference())->toBe('reference-1');
});

it('assigns multiple items to the invoice', function () {
    $this->invoice->addItem(new InvoiceItem(reference: 'reference-1'));
    $this->invoice->addItem(new InvoiceItem(reference: 'reference-2'));

    expect($this->invoice->items()->count())->toBe(2);
    expect($this->invoice->items()->first()->reference())->toBe('reference-1');
    expect($this->invoice->items()->last()->reference())->toBe('reference-2');
});

it('can assign a custom price to an item', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $this->invoice->addItem($item);

    expect($this->invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($this->invoice->items()->first()->price())->toBe(500);
});