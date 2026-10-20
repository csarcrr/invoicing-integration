<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CountryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetCountryIdException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Country\Get;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('cache.default', 'array');
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the countries get all endpoint', function () {
    (new Get(CountryData::from(['iso' => 'PT'])))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'countries/getAll')
        && Str::contains($request->body(), 'company_id=123456'));
});

it('fills the country id and name matching the iso code', function (string $iso, int $expectedId, string $expectedName) {
    $country = (new Get(CountryData::from(['iso' => $iso])))->execute()->getCountry();

    expect($country->id)->toBe($expectedId)
        ->and($country->name)->toBe($expectedName)
        ->and($country->iso)->toBe($iso);
})->with([
    ['PT', 1, 'Portugal'],
    ['pt', 1, 'Portugal'],
    ['ES', 70, 'Espanha'],
]);

it('fails when the iso code is not a moloni country', function () {
    (new Get(CountryData::from(['iso' => 'XX'])))->execute();
})->throws(CouldNotGetCountryIdException::class);
