<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Category;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('sends the category id in the request', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->category()->files('get')));

    Category::get(CategoryData::make(['id' => 29999]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'GET'
            && Str::contains($request->url(), 'products/categories/29999'),
        Provider::MOLONI => Str::contains($request->url(), 'productCategories/getOne')
            && Str::contains($request->body(), 'category_id=29999'),
    });
})->with('providers');

test('maps the provider response onto CategoryData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->category()->files('get')));

    $category = Category::get(CategoryData::make(['id' => 29999]))->execute()->getCategory();

    expect($category)->toBeInstanceOf(CategoryData::class)
        ->and($category->id)->toBe(29999)
        ->and($category->name)->toBe('Produtos')
        ->and($category->parentId)->toBeNull();
})->with('providers');

test('maps the moloni parent id of a subcategory', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse(fixtures()->response()->category()->files('get_child')));

    $category = Category::get(CategoryData::make(['id' => 39999]))->execute()->getCategory();

    expect($category->parentId)->toBe(29999);
});

test('fails when no id is set', function (Provider $provider) {
    Category::get(CategoryData::make([]))->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Category ID is required.');

test('fails when moloni has no category with the id', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse([]));

    Category::get(CategoryData::make(['id' => 1]))->execute();
})->throws(RequestFailedException::class, 'Category not found.');

test('fails when cegid vendus has no category with the id', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    Category::get(CategoryData::make(['id' => 1]))->execute();
})->throws(RequestFailedException::class);
