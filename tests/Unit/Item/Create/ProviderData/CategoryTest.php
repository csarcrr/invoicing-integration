<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('handles an item without a category', function (Provider $provider) {
    $create = fn () => Item::create(ItemData::make(['name' => 'Item Title']))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create()->has('category_id'))->toBeFalse(),
        Provider::MOLONI => expect($create)->toThrow(MissingCategoryException::class),
    };
})->with('providers');
