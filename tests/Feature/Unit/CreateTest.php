<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Unit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the created unit onto UnitData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->unit()->files('create')));

    $unit = Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']))->execute()->get();

    expect($unit)->toBeInstanceOf(UnitData::class)
        ->and($unit->id)->toBe(29999)
        ->and($unit->name)->toBe('Kilograma')
        ->and($unit->shortName)->toBe(match ($provider) {
            Provider::CEGID_VENDUS => null,
            Provider::MOLONI => 'Kg',
        });
})->with('providers');

test('sends the unit to the provider', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->unit()->files('create')));

    Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']))->execute();

    Http::assertSent(match ($provider) {
        Provider::CEGID_VENDUS => fn (Request $request) => $request->method() === 'POST'
            && Str::endsWith($request->url(), 'products/units')
            && Str::contains($request->body(), 'Kilograma'),
        Provider::MOLONI => fn (Request $request) => Str::contains($request->url(), 'measurementUnits/insert')
            && Str::contains($request->body(), 'name=Kilograma')
            && Str::contains($request->body(), 'short_name=Kg'),
    });
})->with('providers');

test('fails with the provider error when the unit is not created', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'Poorly filled parameters']]], 400),
        Provider::MOLONI => mockResponse(['valid' => 0, 'message' => 'Invalid name']),
    });

    Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']))->execute();
})->with('providers')->throws(RequestFailedException::class);
