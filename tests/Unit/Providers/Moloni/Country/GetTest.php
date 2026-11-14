<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CountryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Country\Get;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the countries get all endpoint', function () {
    expect((new Get(CountryData::from(['iso' => 'PT'])))->getUrl())->toBe('countries/getAll');
});
