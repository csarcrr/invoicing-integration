<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ClientAction;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->headers = ['X-Paginator-Items' => 10, 'X-Paginator-Pages' => 5];
});

test('getting list of clients returns expected instances', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fake(mockResponse($response));

    $results = Client::find()->execute();

    expect($results->getList())->toBeInstanceOf(Collection::class)
        ->and($results->getList()->first())->toBeInstanceOf(ClientDataObject::class);
})->with('providers', ['response']);

test('automagically injects provider pagination details into the request', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fake(mockResponse($response, 200));

    ClientAction::find()->execute();

    Http::assertSent(function (Request $request) use ($provider) {
        return match ($provider) {
            Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=1'),
            default => throw new Exception('Provider not supported.')
        };
    });
})->with('providers', ['response']);

test('can fetch the next page', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers);

    $results = Client::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList()->count())->toBe(2)
        ->and($results->getTotalPages())->toBe(5);
})->with('providers', ['response']);

test('can go to the next page and then go back', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers)
        ->push($response, 200, $this->headers);

    $results = Client::find()->execute();
    $results->next()->execute();
    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList()->count())->toBe(5)
        ->and($results->getTotalPages())->toBe(5);
})->with('providers', ['response']);

test('fails when attempting to go above or bellow the allowed pages', function (Provider $provider, string $fixtureName, int $page) {
    Http::fakeSequence()
        ->push(collect([]), 200, $this->headers)
        ->push(collect([]), 200, $this->headers);

    $results = Client::find()->execute();
    $results->page($page)->execute();
})->with('providers', ['response'], [[0], [10]])->throws(NoMorePagesException::class);
