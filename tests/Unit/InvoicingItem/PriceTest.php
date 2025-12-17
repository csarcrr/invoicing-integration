<?php

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->item = new InvoiceItem;
});

it('can assign price', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->price())->toBe(500);
});

it('can assign a percentage discount', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(1000);
    $this->item->setPercentageDiscount(10);

    expect($this->item->percentageDiscount())->toBe(10);
});

it('makes sure percentage discount is not defined when not set', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(1000);

    expect($this->item->percentageDiscount())->toBeNull();
});

it('can assign a fixed discount', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(1000);
    $this->item->setAmountDiscount(200);

    expect($this->item->amountDiscount())->toBe(200);
});

it('makes sure amount discount is not defined when not set', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(1000);

    expect($this->item->amountDiscount())->toBeNull();
});
