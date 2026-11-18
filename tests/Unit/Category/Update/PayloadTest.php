<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Category;

it('builds the update url for the category id', function (Provider $provider) {
    $update = Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos']));

    match ($provider) {
        Provider::VENDUS => expect($update->getUrl())->toBe('products/categories/29999'),
        Provider::MOLONI => expect($update->getUrl())->toBe('productCategories/update'),
    };
})->with('providers');

it('builds the full payload with name and parent id', function (Provider $provider) {
    $payload = Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos', 'parentId' => 10]))->getPayload();

    match ($provider) {
        Provider::VENDUS => expect($payload->toArray())->toBe(['title' => 'Produtos']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['category_id' => 29999, 'name' => 'Produtos', 'parent_id' => 10]),
    };
})->with('providers');

it('omits the parent id from the payload when it is not set', function (Provider $provider) {
    $payload = Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos']))->getPayload();

    match ($provider) {
        Provider::VENDUS => expect($payload->toArray())->toBe(['title' => 'Produtos']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['category_id' => 29999, 'name' => 'Produtos']),
    };
})->with('providers');

it('fails when no id is set', function () {
    Category::update(CategoryData::make(['name' => 'Produtos']));
})->with('providers')->throws(InvalidArgumentException::class, 'Category ID is required.');
