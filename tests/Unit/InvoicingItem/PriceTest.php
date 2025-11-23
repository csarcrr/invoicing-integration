<?php

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('can assign price', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPrice(500);

    expect($item->price())->toBe(500);
});

it('can assign a percentage discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPrice(1000);
    $item->setPercentageDiscount(10);

    expect($item->percentageDiscount())->toBe(10);
});

it('makes sure percentage discount is not defined when not set', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPrice(1000);

    expect($item->percentageDiscount())->toBeNull();
});

it('can assign a fixed discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPrice(1000);
    $item->setAmountDiscount(200);

    expect($item->amountDiscount())->toBe(200);
});

it('makes sure amount discount is not defined when not set', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPrice(1000);

    expect($item->amountDiscount())->toBeNull();
});
