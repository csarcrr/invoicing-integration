<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Category;

it('builds the create url', function (Provider $provider) {
    $create = Category::create(CategoryData::make(['name' => 'Produtos']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create->getUrl())->toBe('products/categories'),
        Provider::MOLONI => expect($create->getUrl())->toBe('productCategories/insert'),
    };
})->with('providers');

it('builds the payload for a top-level category', function (Provider $provider) {
    $payload = Category::create(CategoryData::make(['name' => 'Produtos']))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toBe(['title' => 'Produtos']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['name' => 'Produtos', 'parent_id' => 0]),
    };
})->with('providers');

it('builds the payload with a parent id', function (Provider $provider) {
    $payload = Category::create(CategoryData::make(['name' => 'Produtos', 'parentId' => 29999]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toBe(['title' => 'Produtos']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['name' => 'Produtos', 'parent_id' => 29999]),
    };
})->with('providers');
