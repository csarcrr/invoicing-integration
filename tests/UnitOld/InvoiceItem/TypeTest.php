<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->item = new Item;
});

it('can assign an item type', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setType(ItemType::Product);

    expect($this->item->type())->toBe(ItemType::Product);
});

it('sets item type default to Product', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->type())->toBe(ItemType::Product);
});

it('can assign all item types', function ($type) {
    $type = ItemType::from($type);
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setType($type);

    expect($this->item->type())->toBe($type);
})->with(ItemType::options());
