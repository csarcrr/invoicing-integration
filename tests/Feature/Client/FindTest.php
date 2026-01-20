<?php
declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Http\Client\Request;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


test('getting list of clients returns expected instances', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fake(mockResponse($response, 200));


    $results = Client::find()->execute();

    expect($results->getList())->toBeInstanceOf(Collection::class)
        ->and($results->getList()->first())->toBeInstanceOf(ClientDataObject::class);
})->with('providers', ['response']);

test('automagically injects provider pagination details into the request', function (Provider $provider, string $fixtureName) {
    for ($i = 0; $i < 2; $i++) {
        $response[] = fixtures()->response()->client()->files($fixtureName);
    }

    Http::fake(mockResponse($response, 200));

    Client::find()->execute();

    Http::assertSent(function (Request $request) use ($provider) {
        return match($provider) {
            Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=1'),
            default => throw new Exception('Provider not supported.')
        };
    });
})->with('providers', ['response']);
