<?php

use CsarCrr\InvoicingIntegration\InvoicingItem;

it('can assign description', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setDescription('Test Description');

    expect($item->description())->toBe('Test Description');
});

it('description is null by default', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->description())->toBeNull();
});
