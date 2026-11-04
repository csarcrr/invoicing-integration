<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->headers = ['X-Paginator-Items' => 10, 'X-Paginator-Pages' => 5];
    $this->filters = ItemData::make(['category' => CategoryData::make(['id' => 12])]);
});

test('getting list of items returns expected instances', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($fixtureName)));

    $results = Item::find($this->filters)->execute();

    expect($results->getList())->toBeInstanceOf(Collection::class)
        ->and($results->getList()->first())->toBeInstanceOf(ItemData::class);
})->with('providers', ['response_multiple']);

test('automagically injects provider pagination details into the request', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($fixtureName), 200));

    Item::find($this->filters)->execute();

    Http::assertSent(function (Request $request) use ($provider) {
        return match ($provider) {
            Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=1'),
            Provider::MOLONI => Str::contains($request->url(), 'products/getAll')
                && Str::contains($request->body(), 'category_id=12')
                && Str::contains($request->body(), 'qty=50')
                && Str::contains($request->body(), 'offset=0'),
        };
    });
})->with('providers', ['response_multiple']);

test('searches moloni products when a search field is set', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse(fixtures()->response()->item()->files('response_multiple')));

    Item::find(ItemData::make(['name' => 'Widget']))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getBySearch')
        && Str::contains($request->body(), 'search=Widget')
        && ! Str::contains($request->body(), 'category_id'));
});

test('reports the total pages the provider returns', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($fixtureName), 200, $this->headers));

    $results = Item::find($this->filters)->execute();

    expect($results->getTotalPages())->toBe(match ($provider) {
        Provider::CEGID_VENDUS => 5,
        Provider::MOLONI => null,
    });
})->with('providers', ['response_multiple']);

test('can fetch the next page', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->item()->files($fixtureName)[0];
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers);

    $results = Item::find($this->filters)->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList()->count())->toBe(2);
})->with('providers', ['response_multiple']);

test('can go to the next page and then go back', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->item()->files($fixtureName)[0];
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers)
        ->push($response, 200, $this->headers);

    $results = Item::find($this->filters)->execute();
    $results->next()->execute();
    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList()->count())->toBe(5);
})->with('providers', ['response_multiple']);

test('fails when attempting to go below the first page', function (Provider $provider) {
    Http::fakeSequence()
        ->push([], 200, $this->headers)
        ->push([], 200, $this->headers);

    $results = Item::find($this->filters)->execute();
    $results->page(0)->execute();
})->with('providers')->throws(NoMorePagesException::class);

// Moloni does not report its total pages, so there is no known upper limit to exceed.
test('fails when attempting to go above the total pages', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fakeSequence()
        ->push([], 200, $this->headers)
        ->push([], 200, $this->headers);

    $results = Item::find($this->filters)->execute();
    $results->page(10)->execute();
})->throws(NoMorePagesException::class);

test('maps the vendus items onto the item list', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(fixtures()->response()->item()->files('response_multiple')));

    $list = Item::find()->execute()->getList();

    expect($list)->toHaveCount(3)
        ->and($list->first()->id)->toBe(1001)
        ->and($list->first()->name)->toBe('Item Title')
        ->and($list->first()->description)->toBe('Item Description')
        ->and($list->first()->reference)->toBe('reference-1')
        ->and($list->first()->barcode)->toBe('barcode-1')
        ->and($list->first()->type)->toBe(ItemType::Product)
        ->and($list->first()->tax)->toBe(ItemTax::EXEMPT)
        ->and($list->first()->price)->toBe(2000)
        ->and($list->first()->controlStock)->toBeTrue()
        ->and($list->first()->enabled)->toBeTrue()
        ->and($list->get(1)->type)->toBe(ItemType::Service)
        ->and($list->get(1)->tax)->toBe(ItemTax::NORMAL)
        ->and($list->get(1)->price)->toBe(4500)
        ->and($list->get(1)->controlStock)->toBeFalse()
        ->and($list->last()->enabled)->toBeFalse();
});

test('maps the moloni products onto the item list', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse(fixtures()->response()->item()->files('response_multiple')));

    $list = Item::find($this->filters)->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first()->id)->toBe(1542)
        ->and($list->first()->name)->toBe('Wireless Bluetooth Headphones')
        ->and($list->first()->reference)->toBe('WBH-2026-BLK')
        ->and($list->first()->barcode)->toBe('5601234567890')
        ->and($list->first()->type)->toBe(ItemType::Product)
        ->and($list->first()->tax)->toBe(ItemTax::NORMAL)
        ->and($list->first()->price)->toBe(2460)
        ->and($list->first()->enabled)->toBeTrue()
        ->and($list->first()->description)->toBe('Over-ear headphones with noise cancelling.')
        ->and($list->first()->category->id)->toBe(1)
        ->and($list->first()->controlStock)->toBeTrue()
        ->and($list->last()->id)->toBe(1543)
        ->and($list->last()->controlStock)->toBeFalse()
        ->and($list->last()->type)->toBe(ItemType::Service)
        ->and($list->last()->tax)->toBeNull()
        ->and($list->last()->price)->toBeNull()
        ->and($list->last()->enabled)->toBeFalse();
});

test('maps a moloni tax by its configured tax id when finding items', function () {
    mockConfiguration(Provider::MOLONI);

    $response = fixtures()->response()->item()->files('response_multiple');
    $response[0]['taxes'][0]['tax_id'] = 5;
    $response[0]['taxes'][0]['tax']['tax_id'] = 5;
    $response[0]['taxes'][0]['tax']['vat_type'] = '0';

    Http::fake(mockResponse($response));

    $results = Item::find($this->filters)->execute();

    expect($results->getList()->first()->tax)->toBe(ItemTax::EXEMPT);
});

test('maps the tax exemption of exempt items when finding items', function (Provider $provider, string $fixtureName) {
    $response = fixtures()->response()->item()->files($fixtureName);

    match ($provider) {
        Provider::CEGID_VENDUS => $response[0] = [...$response[0], 'tax_id' => 'ISE', 'tax_exemption' => 'M01'],
        Provider::MOLONI => $response[0] = [...$response[0], 'exemption_reason' => 'M01', 'taxes' => [[...$response[0]['taxes'][0], 'tax_id' => 5]]],
    };

    Http::fake(mockResponse($response));

    $item = Item::find($this->filters)->execute()->getList()->first();

    expect($item->tax)->toBe(ItemTax::EXEMPT)
        ->and($item->taxExemptionReason)->toBe(TaxExemptionReason::M01);
})->with('providers', ['response_multiple']);

test('returns an empty list when cegid vendus finds no items', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    $results = Item::find(ItemData::make(['reference' => 'missing']))->execute();

    expect($results->getList())->toBeEmpty();
});

test('fails when cegid vendus answers 404 with an error other than no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A002', 'message' => 'Not found']]], 404));

    Item::find(ItemData::make(['reference' => 'missing']))->execute();
})->throws(RequestFailedException::class);

test('keeps the cegid vendus total pages when a page request fails', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fakeSequence()
        ->push([], 200, $this->headers)
        ->push([], 500);

    $results = Item::find($this->filters)->execute();

    expect(fn () => $results->next()->execute())->toThrow(FailedReachingProviderException::class)
        ->and($results->getTotalPages())->toBe(5);
});
