<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('builds the delete url for the item id', function (Provider $provider) {
    $delete = Item::delete(ItemData::from(['id' => 99999]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($delete->getUrl())->toBe('products/99999'),
        Provider::MOLONI => expect($delete->getUrl())->toBe('products/delete'),
    };
})->with('providers');

it('sends the item id in the payload', function (Provider $provider) {
    $payload = Item::delete(ItemData::from(['id' => 99999]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toBe([]),
        Provider::MOLONI => expect($payload->toArray())->toBe(['product_id' => 99999]),
    };
})->with('providers');

it('fails when no id is set', function () {
    Item::delete(ItemData::from([]));
})->with('providers')->throws(InvalidArgumentException::class, 'Item ID is required.');
