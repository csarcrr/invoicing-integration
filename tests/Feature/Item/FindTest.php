<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
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
        ->and($results->getList()->count())->toBe(2)
        ->and($results->getTotalPages())->toBe(match ($provider) {
            Provider::CEGID_VENDUS => 5,
            Provider::MOLONI => null,
        });
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
        ->and($results->getList()->count())->toBe(5)
        ->and($results->getTotalPages())->toBe(match ($provider) {
            Provider::CEGID_VENDUS => 5,
            Provider::MOLONI => null,
        });
})->with('providers', ['response_multiple']);

test('fails when attempting to go above or below the allowed pages', function (Provider $provider, string $fixtureName, int $page) {
    if ($provider === Provider::MOLONI && $page > 1) {
        $this->markTestSkipped('Moloni does not report the total pages, so there is no upper limit.');
    }

    Http::fakeSequence()
        ->push(collect([]), 200, $this->headers)
        ->push(collect([]), 200, $this->headers);

    $results = Item::find($this->filters)->execute();
    $results->page($page)->execute();
})->with('providers', ['response_multiple'], [[0], [10]])->throws(NoMorePagesException::class);

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
        ->and($list->last()->id)->toBe(1543)
        ->and($list->last()->type)->toBe(ItemType::Service)
        ->and($list->last()->tax)->toBeNull()
        ->and($list->last()->price)->toBeNull();
});
