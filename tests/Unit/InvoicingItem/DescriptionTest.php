<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign description', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setDescription('Test Description');

    expect($item->description())->toBe('Test Description');
});

it('has description null by default', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->description())->toBeNull();
});
