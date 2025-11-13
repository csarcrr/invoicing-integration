<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign price', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->price())->toBe(500);
});

it('can assign a percentage discount', function () {})->todo();

it('can assign a fixed discount', function () {})->todo();
