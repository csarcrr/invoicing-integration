<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Unit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the provider response onto a list of UnitData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->unit()->files('response_multiple')));

    $list = Unit::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(UnitData::class)
        ->and($list->pluck('id')->toArray())->toBe([29999, 19999])
        ->and($list->pluck('name')->toArray())->toBe(['Uni', 'Kg'])
        ->and($list->pluck('shortName')->toArray())->toBe(match ($provider) {
            Provider::CEGID_VENDUS => [null, null],
            Provider::MOLONI => ['Uni.', 'Kg'],
        });
})->with('providers');

test('sends the request to the units endpoint', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Unit::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'GET' && Str::contains($request->url(), 'products/units'),
        Provider::MOLONI => Str::contains($request->url(), 'measurementUnits/getAll'),
    });
})->with('providers');

test('returns an empty list when the provider has no units', function (Provider $provider) {
    Http::fake(mockResponse([]));

    expect(Unit::find()->execute()->getList())->toBeEmpty();
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(Unit::find()->execute()->getList())->toBeEmpty();
});
