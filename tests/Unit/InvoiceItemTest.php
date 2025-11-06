<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\InvoicingItem;

it('can assign quantity', function () {
    $item = new InvoicingItem(reference: 'reference-1', quantity: 3);
    $item->setPrice(500);

    expect($item->quantity)->toBe(3);
});

it('defaults quantity to 1', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->quantity)->toBe(1);
});

it('can assign reference', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->reference)->toBe('reference-1');
});

it('can assign price', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->price())->toBe(500);
});

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

it('can assign an item type', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setType(DocumentItemType::Product);

    expect($item->type())->toBe(DocumentItemType::Product);
});

it('item type defaults to Product', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->type())->toBe(DocumentItemType::Product);
});

it('can assign all item types', function ($type) {
    $type = DocumentItemType::from($type);
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    $item->setType($type);
    expect($item->type())->toBe($type);
})->with(DocumentItemType::options());
