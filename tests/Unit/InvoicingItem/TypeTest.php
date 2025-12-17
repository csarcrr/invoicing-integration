<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->item = new InvoiceItem;
});

it('can assign an item type', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setType(DocumentItemType::Product);

    expect($this->item->type())->toBe(DocumentItemType::Product);
});

it('sets item type default to Product', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->type())->toBe(DocumentItemType::Product);
});

it('can assign all item types', function ($type) {
    $type = DocumentItemType::from($type);
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setType($type);

    expect($this->item->type())->toBe($type);
})->with(DocumentItemType::options());
