<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Category;
use Illuminate\Support\Facades\Http;

test('maps the created category onto CategoryData', function () {
    Http::fake(mockResponse(fixtures()->response()->category()->files('create')));

    $category = Category::create(CategoryData::make(['name' => 'Produtos']))->execute()->getCategory();

    expect($category)->toBeInstanceOf(CategoryData::class)
        ->and($category->id)->toBe(29999)
        ->and($category->name)->toBe('Produtos')
        ->and($category->parentId)->toBeNull();
})->with('providers');

test('fails with the provider error when the category is not created', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'Poorly filled parameters']]], 400),
        Provider::MOLONI => mockResponse(['valid' => 0, 'message' => 'Invalid name']),
    });

    Category::create(CategoryData::make(['name' => 'Produtos']))->execute();
})->with('providers')->throws(RequestFailedException::class);
