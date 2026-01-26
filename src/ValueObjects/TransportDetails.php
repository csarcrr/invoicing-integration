<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use League\ISO3166\ISO3166;

class TransportDetails
{
    protected ?string $type = null;

    protected ?string $vehicleLicensePlate = null;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $data = [
        'origin' => [
            'date' => null,
            'time' => null,
            'address' => null,
            'city' => null,
            'postalCode' => null,
            'country' => null,
        ],
        'destination' => [
            'date' => null,
            'time' => null,
            'address' => null,
            'city' => null,
            'postalCode' => null,
            'country' => null,
        ],
    ];

    public function origin(): self
    {
        $this->type = 'origin';

        return $this;
    }

    public function destination(): self
    {
        $this->type = 'destination';

        return $this;
    }

    public function getVehicleLicensePlate(): ?string
    {
        return $this->vehicleLicensePlate;
    }

    public function vehicleLicensePlate(string $vehicleLicensePlate): self
    {
        $this->vehicleLicensePlate = $vehicleLicensePlate;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->data[$this->type]['address'];
    }

    public function address(string $address): self
    {
        $this->data[$this->type]['address'] = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->data[$this->type]['city'];
    }

    public function city(string $city): self
    {
        $this->data[$this->type]['city'] = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->data[$this->type]['postalCode'];
    }

    public function postalCode(string $postalCode): self
    {
        $this->data[$this->type]['postalCode'] = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->data[$this->type]['country'];
    }

    /**
     * @throws InvalidCountryException
     */
    public function country(string $country): self
    {
        try {
            $data = (new ISO3166)->alpha2(strtolower($country));
        } catch (\Exception $e) {
            throw new InvalidCountryException;
        }

        $this->data[$this->type]['country'] = $data['alpha2'];

        return $this;
    }

    public function getDate(): ?Carbon
    {
        return $this->data[$this->type]['date'] ?? null;
    }

    public function date(Carbon $date): self
    {
        $this->data[$this->type]['date'] = $date;

        return $this;
    }

    public function getTime(): ?Carbon
    {
        return $this->data[$this->type]['time'] ?? null;
    }

    public function time(Carbon $time): self
    {
        $this->data[$this->type]['time'] = $time;

        return $this;
    }
}
