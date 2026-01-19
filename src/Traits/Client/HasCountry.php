<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use League\ISO3166\ISO3166;

trait HasCountry
{
    protected ?string $country = null;

    public function country(string $country): self
    {
        try {
            (new ISO3166)->alpha2(strtolower($country));
        } catch (\Exception $e) {
            throw new InvalidCountryException;
        }

        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
}
