<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
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
        'tax' => ItemTax::NORMAL,
    ]))->execute()->getItem();

    expect($data->id)->toBe(1542)
        ->and($data->name)->toBeString()
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with('providers', ['create']);

test('reads the created item as enabled', function (Provider $provider, string $fixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($fixture)));

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute()->getItem();

    expect($data->enabled)->toBeTrue();
})->with('providers', ['create']);

test('reads a vendus item created as disabled', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $response = fixtures()->response()->item()->files('create');
    $response['status'] = 'off';

    Http::fake(mockResponse($response));

    $data = Item::create(ItemData::make(['name' => 'Item Title', 'enabled' => false]))->execute()->getItem();

    expect($data->enabled)->toBeFalse();
});

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
