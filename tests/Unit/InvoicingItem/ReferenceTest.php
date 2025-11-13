<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign reference', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    expect($item->reference())->toBe('reference-1');
});

it('can assign reference with setter', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');

    expect($item->reference())->toBe('reference-1');
})->todo();
