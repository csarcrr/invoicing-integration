<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Category;

it('builds the payload for the top level on the first page', function (Provider $provider) {
    $find = Category::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getPayload()->toArray())->toBe(['page' => 1]),
        Provider::MOLONI => expect($find->getPayload()->toArray())->toBe(['parent_id' => 0]),
    };
})->with('providers');

it('sends the parent id to moloni', function () {
    mockConfiguration(Provider::MOLONI);

    $find = Category::find(CategoryData::make(['parentId' => 29999]));

    expect($find->getPayload()->toArray())->toBe(['parent_id' => 29999]);
});

it('does not send the parent id to cegid vendus', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $find = Category::find(CategoryData::make(['parentId' => 29999]));

    expect($find->getPayload()->toArray())->toBe(['page' => 1]);
});

it('moves the cegid vendus page when changing page', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $find = Category::find()->next();

    expect($find->getPayload()->toArray())->toBe(['page' => 2]);
});
