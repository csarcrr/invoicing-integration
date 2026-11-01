<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
    Cache::flush();
});

it('asks for human readable errors', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    $recorded = Http::recorded(function (Request $request) {
        return Str::contains($request->url(), 'human_errors=true');
    });

    expect(! empty($recorded->first()[0]))->toBeTrue();
});
