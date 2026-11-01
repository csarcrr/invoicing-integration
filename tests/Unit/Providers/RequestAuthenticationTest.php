<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('authenticates the provider requests', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    Item::get(ItemData::make(['id' => 123]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->hasHeader('Authorization', 'Bearer test-api-key'),
        Provider::MOLONI => Str::contains($request->url(), 'access_token')
            && Str::contains($request->body(), 'company_id=123456'),
    });
})->with('providers');
