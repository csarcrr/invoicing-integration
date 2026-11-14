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
    protected string $url;

    public function __construct(protected CountryData $country)
    {
        $this->buildUrl();
    }

    /**
     * @throws CouldNotGetCountryIdException
     */
    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl());

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $countries */
        $countries = $response->json();

        $match = collect($countries)->first(
            fn (array $country): bool => Str::lower((string) $country['iso_3166_1']) === Str::lower($this->country->iso)
        );

        throw_if(is_null($match), CouldNotGetCountryIdException::class);

        $this->country->id = (int) $match['country_id'];
        $this->country->name = (string) $match['name'];

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCountry(): CountryData
    {
        return $this->country;
    }

    protected function buildUrl(): void
    {
        $this->url = 'countries/getAll';
    }
}
