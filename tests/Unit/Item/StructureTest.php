<?php

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('is an instance of the correct when creating an item', function (Provider $provider) {
    expect(Item::create(ItemData::from([])))->toBeInstanceOf(ShouldCreateItem::class);
})->with('providers');

it('is an instance of the correct when finding an item', function (Provider $provider) {
    expect(Item::find())->toBeInstanceOf(ShouldFindItem::class);
})->with('providers');

it('is an instance of the correct when getting an item', function (Provider $provider) {
    expect(Item::get(ItemData::make(['id' => 1])))->toBeInstanceOf(ShouldGetItem::class);
})->with('providers');

it('is an instance of the correct when updating an item', function (Provider $provider) {
    expect(Item::update(ItemData::make(['id' => 1])))->toBeInstanceOf(ShouldUpdateItem::class);
})->with('providers');
