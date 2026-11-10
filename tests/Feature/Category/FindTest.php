<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Category;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the provider response onto a list of CategoryData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->category()->files('response_multiple')));

    $list = Category::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(CategoryData::class)
        ->and($list->pluck('id')->toArray())->toBe([29999, 19999])
        ->and($list->pluck('name')->toArray())->toBe(['Produtos', 'Serviços'])
        ->and($list->pluck('parentId')->toArray())->toBe([null, null]);
})->with('providers');

test('maps the moloni parent id of a subcategory', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse([fixtures()->response()->category()->files('get_child')]));

    $category = Category::find(CategoryData::make(['parentId' => 29999]))->execute()->getList()->first();

    expect($category->id)->toBe(39999)
        ->and($category->name)->toBe('Fast-Food')
        ->and($category->parentId)->toBe(29999);
});

test('sends the request to the categories endpoint', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Category::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'GET'
            && Str::contains($request->url(), 'products/categories')
            && Str::contains($request->url(), 'page=1'),
        Provider::MOLONI => Str::contains($request->url(), 'productCategories/getAll')
            && Str::contains($request->body(), 'parent_id=0'),
    });
})->with('providers');

test('returns an empty list when the provider has no categories', function (Provider $provider) {
    Http::fake(mockResponse([]));

    expect(Category::find()->execute()->getList())->toBeEmpty();
})->with('providers');

test('returns an empty list when cegid vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(Category::find()->execute()->getList())->toBeEmpty();
});

test('fails when cegid vendus answers 404 with an error other than no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A002', 'message' => 'Not found']]], 404));

    Category::find()->execute();
})->throws(RequestFailedException::class);

test('reports the total pages the provider returns', function (Provider $provider) {
    Http::fake(mockResponse(
        fixtures()->response()->category()->files('response_multiple'),
        200,
        ['X-Paginator-Items' => 10, 'X-Paginator-Pages' => 5],
    ));

    $results = Category::find()->execute();

    expect($results->getTotalPages())->toBe(match ($provider) {
        Provider::CEGID_VENDUS => 5,
        Provider::MOLONI => 1,
    });
})->with('providers');

test('fetches the next cegid vendus page', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $categories = fixtures()->response()->category()->files('response_multiple');
    $headers = ['X-Paginator-Items' => 3, 'X-Paginator-Pages' => 2];

    Http::fakeSequence()
        ->push($categories, 200, $headers)
        ->push([$categories[0]], 200, $headers);

    $results = Category::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList())->toHaveCount(1);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'page=2'));
});

test('fails when moving past the single moloni page', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse(fixtures()->response()->category()->files('response_multiple')));

    Category::find()->execute()->next();
})->throws(NoMorePagesException::class);

test('fails when attempting to go below the first page', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Category::find()->execute()->page(0);
})->with('providers')->throws(NoMorePagesException::class);
