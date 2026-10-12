<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException as VendusCouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetUnitIdException as MoloniCouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

it('fails when unit is not found', function (Provider $provider) {
    config()->set('invoicing-integration.providers.'.$provider->value.'.units', []);

    $expectedException = match ($provider) {
        Provider::CEGID_VENDUS => VendusCouldNotGetUnitIdException::class,
        Provider::MOLONI => MoloniCouldNotGetUnitIdException::class,
    };

    expect(fn () => Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'unit' => Unit::KG,
    ]))->getPayload())->toThrow($expectedException);
})->with('providers');

it('keeps the unit after creating the item', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->item()->files('create')));

    $item = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
        'unit' => Unit::UNIT,
    ]))->execute()->getItem();

    expect($item->id)->toBeInt()
        ->and($item->unit)->toBe(Unit::UNIT);
})->with('providers');
