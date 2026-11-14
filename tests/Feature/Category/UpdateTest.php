<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Category;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the updated category onto CategoryData', function () {
    Http::fake(mockResponse(fixtures()->response()->category()->files('create')));

    $category = Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos']))->execute()->getCategory();

    expect($category)->toBeInstanceOf(CategoryData::class)
        ->and($category->id)->toBe(29999)
        ->and($category->name)->toBe('Produtos')
        ->and($category->parentId)->toBeNull();
})->with('providers');

test('sends the update to the provider', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->category()->files('create')));

    Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos']))->execute();

    Http::assertSent(match ($provider) {
        Provider::CEGID_VENDUS => fn (Request $request) => $request->method() === 'PATCH'
            && Str::endsWith($request->url(), 'products/categories/29999')
            && Str::contains($request->body(), 'Produtos'),
        Provider::MOLONI => fn (Request $request) => Str::contains($request->url(), 'productCategories/update')
            && Str::contains($request->body(), 'category_id=29999')
            && Str::contains($request->body(), 'name=Produtos'),
    });
})->with('providers');

test('fails with the provider error when the category is not updated', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'Poorly filled parameters']]], 400),
        Provider::MOLONI => mockResponse(['valid' => 0, 'message' => 'Invalid name']),
    });

    Category::update(CategoryData::make(['id' => 29999, 'name' => 'Produtos']))->execute();
})->with('providers')->throws(RequestFailedException::class);
