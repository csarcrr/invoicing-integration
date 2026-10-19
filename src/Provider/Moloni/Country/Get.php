<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Country;

use CsarCrr\InvoicingIntegration\Data\CountryData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetCountryIdException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function collect;
use function throw_if;

class Get
{
    public function __construct(protected CountryData $country) {}

    /**
     * @throws CouldNotGetCountryIdException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('countries/getAll');
dd($response->json()[0]);
        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $countries */
        $countries = $response->json();

        $match = collect($countries)->first(
            fn (array $country): bool => Str::lower((string) $country['iso_3166_1']) === Str::lower($this->country->iso)
        );

        throw_if(is_null($match), CouldNotGetCountryIdException::class);

        $this->country->id = (int) $match['country_id'];

        return $this;
    }

    public function getCountry(): CountryData
    {
        return $this->country;
    }
}
