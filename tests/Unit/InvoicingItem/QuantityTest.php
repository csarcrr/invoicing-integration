<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign quantity', function () {
    $item = new InvoiceItem(reference: 'reference-1', quantity: 3);
    $item->setPrice(500);

    expect($item->quantity())->toBe(3);
});

it('can assign quantity with setter', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setQuantity(5);

    expect($item->quantity())->toBe(5);
})->todo();

it('defaults quantity to 1', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->quantity())->toBe(1);
});
