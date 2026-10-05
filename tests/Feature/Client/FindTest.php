<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\LaravelData\Optional;

beforeEach(function () {
    $this->headers = ['X-Paginator-Items' => 10, 'X-Paginator-Pages' => 5];
    $this->filters = ClientData::from(['vat' => '215783920']);
});

test('getting list of clients returns expected instances', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($fixtureName)));

    $results = Client::find($this->filters)->execute();

    expect($results->getList())->toBeInstanceOf(Collection::class)
        ->and($results->getList()->first())->toBeInstanceOf(ClientData::class);
})->with('providers', ['response_multiple']);

test('automagically injects provider pagination details into the request', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fake(mockResponse($response, 200));

    Client::find($this->filters)->execute();

    Http::assertSent(function (Request $request) use ($provider) {
        return match ($provider) {
            Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=1'),
            Provider::MOLONI => Str::contains($request->body(), 'offset=0'),
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

    $results = Client::find($this->filters)->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList()->count())->toBe(2);

    match ($provider) {
        Provider::CEGID_VENDUS => expect($results->getTotalPages())->toBe(5),
        Provider::MOLONI => expect($results->getTotalPages())->toBeNull(),
    };
})->with('providers', ['response']);

test('can go to the next page and then go back', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 5; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fakeSequence()
        ->push($response, 200, $this->headers)
        ->push(collect($response)->take(2)->toArray(), 200, $this->headers)
        ->push($response, 200, $this->headers);

    $results = Client::find($this->filters)->execute();
    $results->next()->execute();
    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList()->count())->toBe(5);

    match ($provider) {
        Provider::CEGID_VENDUS => expect($results->getTotalPages())->toBe(5),
        Provider::MOLONI => expect($results->getTotalPages())->toBeNull(),
    };
})->with('providers', ['response']);

test('maps all response fields onto the returned ClientData', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($fixtureName)));

    $client = Client::find($this->filters)->execute()->getList()->first();

    expect($client->name)->toBe('Marta Silva')
        ->and($client->email)->toBe('marta.silva@example.com')
        ->and($client->vat)->toBe('215783920');

    match ($provider) {
        Provider::CEGID_VENDUS => expect($client->emailNotification)->toBeTrue()
            ->and($client->irsRetention)->toBeFalse(),
        Provider::MOLONI => expect($client->id)->toBe('12001')
            ->and($client->country)->toBe('PT'),
    };
})->with('providers', ['response_multiple']);

test('absent response fields remain Optional on the returned ClientData', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($fixtureName)));

    $client = Client::find($this->filters)->execute()->getList()->first();

    expect($client->id)->toBeInstanceOf(Optional::class)
        ->and($client->email)->toBeInstanceOf(Optional::class)
        ->and($client->vat)->toBeInstanceOf(Optional::class)
        ->and($client->city)->toBeInstanceOf(Optional::class)
        ->and($client->externalReference)->toBeInstanceOf(Optional::class);
})->with('providers', ['response_sparse']);

test('fails when attempting to go below the first page', function (Provider $provider) {
    Http::fakeSequence()
        ->push(collect([]), 200, $this->headers);

    $results = Client::find($this->filters)->execute();
    $results->page(0)->execute();
})->with('providers')->throws(NoMorePagesException::class);

test('fails when attempting to go above the known total of pages', function (Provider $provider) {
    Http::fakeSequence()
        ->push(collect([]), 200, $this->headers)
        ->push(collect([]), 200, $this->headers);

    $results = Client::find($this->filters)->execute();
    $goAbove = fn () => $results->page(10)->execute();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($goAbove)->toThrow(NoMorePagesException::class),
        Provider::MOLONI => expect($goAbove)->not->toThrow(NoMorePagesException::class),
    };
})->with('providers');

test('fails when no search criteria is set', function (Provider $provider) {
    Http::fake(mockResponse([]));

    $find = fn () => Client::find()->execute();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->not->toThrow(InvalidArgumentException::class),
        Provider::MOLONI => expect($find)->toThrow(InvalidArgumentException::class, 'A vat, name or id is required to search clients.'),
    };
})->with('providers');
