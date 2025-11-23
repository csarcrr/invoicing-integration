<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('can assign an item type', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setType(DocumentItemType::Product);

    expect($item->type())->toBe(DocumentItemType::Product);
});

it('sets item type default to Product', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->type())->toBe(DocumentItemType::Product);
});

it('can assign all item types', function ($type) {
    $type = DocumentItemType::from($type);
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setType($type);

    expect($item->type())->toBe($type);
})->with(DocumentItemType::options());
