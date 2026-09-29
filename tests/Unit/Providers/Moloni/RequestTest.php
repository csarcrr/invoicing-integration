<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('cache.default', 'array');
    mockConfiguration(Provider::MOLONI);
    Cache::flush();
});

it('has the payload properly defined in the request', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    $recorded = Http::recorded(function (Request $request, Response $response) {
        return Str::contains($request->url(), 'access_token') && Str::contains($request->body(), 'company_id=123') && $response->successful();
    });

    expect(!empty($recorded->first()[0]))->toBeTrue();
});
