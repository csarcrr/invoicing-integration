<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasCity
{
    protected string $city;

    public function city(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}
