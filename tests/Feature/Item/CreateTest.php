<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('creates an item', function (Provider $provider, string $fixture) {
    $payload = fixtures()->response()->item()->files($fixture);

    Http::fake(mockResponse($payload));

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'description' => 'Item Description',
        'barcode' => 'barcode-1',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
        'tax' => CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax::NORMAL,
    ]))->execute()->getItem();

    expect($data->id)->toBe(match ($provider) {
        Provider::CEGID_VENDUS => 111111,
        Provider::MOLONI => 1542,
    })
        ->and($data->name)->toBeString()
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with('providers', ['create']);

test('sends the item to the provider', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->item()->files('create')));

    Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'products')
            && $request['category_id'] === 1,
        Provider::MOLONI => Str::contains($request->url(), 'products/insert')
            && Str::contains($request->body(), 'category_id=1')
            && Str::contains($request->body(), 'company_id=123456'),
    });
})->with('providers');
