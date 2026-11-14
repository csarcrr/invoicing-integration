<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

test('a delete request sends one request and throws nothing', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(''),
        Provider::MOLONI => mockResponse(fixtures()->response()->item()->files('delete')),
    });

    Item::delete(ItemData::from(['id' => 99999]))->execute();

    Http::assertSentCount(1);
})->with('providers');

test('fails with the provider error when the item is not deleted', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 400),
        Provider::MOLONI => mockResponse(['valid' => 0, 'message' => 'Item is referenced by a document']),
    });

    Item::delete(ItemData::from(['id' => 99999]))->execute();
})->with('providers')->throws(RequestFailedException::class);
