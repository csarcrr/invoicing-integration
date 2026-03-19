<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->headers = ['X-Paginator-Items' => 10, 'X-Paginator-Pages' => 5];
});

test('getting list of items returns expected instances', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($fixtureName)));

    $results = Item::find()->execute();

    expect($results->getList())->toBeInstanceOf(Collection::class)
        ->and($results->getList()->first())->toBeInstanceOf(ItemData::class);
})->with('providers', ['response_multiple']);

test('automagically injects provider pagination details into the request', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->item()->files($fixtureName);
    }

    Http::fake(mockResponse($response, 200));

    Item::find()->execute();

    Http::assertSent(function (Request $request) use ($provider) {
        return match ($provider) {
            Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=1'),
            default => throw new Exception('ProviderConfigurationService not supported.')
        };
    });
})->with('providers', ['response_multiple']);

test('can fetch the next page', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->item()->files($fixtureName);
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers);

    $results = Item::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList()->count())->toBe(2)
        ->and($results->getTotalPages())->toBe(5);
})->with('providers', ['response_multiple']);

test('can go to the next page and then go back', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->item()->files($fixtureName);
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers)
        ->push($response, 200, $this->headers);

    $results = Item::find()->execute();
    $results->next()->execute();
    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList()->count())->toBe(5)
        ->and($results->getTotalPages())->toBe(5);
})->with('providers', ['response_multiple']);

test('fails when attempting to go above or below the allowed pages', function (Provider $provider, string $fixtureName, int $page) {
    Http::fakeSequence()
        ->push(collect([]), 200, $this->headers)
        ->push(collect([]), 200, $this->headers);

    $results = Item::find()->execute();
    $results->page($page)->execute();
})->with('providers', ['response_multiple'], [[0], [10]])->throws(NoMorePagesException::class);
