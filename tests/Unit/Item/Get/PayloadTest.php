<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('sends the item id in the request', function (Provider $provider) {
    $get = Item::get(ItemData::make(['id' => 999999]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($get->getUrl())->toBe('/products/999999')
            ->and($get->getPayload()->toArray())->toBe([]),
        Provider::MOLONI => expect($get->getUrl())->toBe('products/getOne')
            ->and($get->getPayload()->toArray())->toBe(['product_id' => 999999, 'with_invisible' => 1]),
    };
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $item = ItemData::make([]);

    Item::get($item)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Item ID is required.');
