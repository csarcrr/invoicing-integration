<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use League\ISO3166\ISO3166;

class InvoiceTransportDetails
{
    protected ?string $type = null;

    protected ?string $vehicleLicensePlate = null;

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

    public function vehicleLicensePlate(?string $vehicleLicensePlate = null): ?string
    {
        if (! $vehicleLicensePlate) {
            return $this->vehicleLicensePlate;
        }

        return $this->vehicleLicensePlate = $vehicleLicensePlate;
    }

    public function address(?string $address = null): ?string
    {
        if (! $address) {
            return $this->data[$this->type]['address'];
        }

        return $this->data[$this->type]['address'] = $address;
    }

    public function city(?string $city = null): ?string
    {
        if (! $city) {
            return $this->data[$this->type]['city'];
        }

        return $this->data[$this->type]['city'] = $city;
    }

    public function postalCode(?string $postalCode = null): ?string
    {
        if (! $postalCode) {
            return $this->data[$this->type]['postalCode'];
        }

        return $this->data[$this->type]['postalCode'] = $postalCode;
    }

    public function country(?string $country = null): ?string
    {
        if (! $country) {
            return $this->data[$this->type]['country'];
        }

        try {
            $data = (new ISO3166)->alpha2(strtolower($country));
        } catch (\Exception $e) {
            throw new InvalidCountryException;
        }

        return $this->data[$this->type]['country'] = $data['alpha2'];
    }

    public function date(?Carbon $date = null): ?Carbon
    {
        if (! $date) {
            return $this->data[$this->type]['date'] ?? null;
        }

        return $this->data[$this->type]['date'] = $date;
    }

    public function time(?Carbon $time = null): ?Carbon
    {
        if (! $time) {
            return $this->data[$this->type]['time'] ?? null;
        }

        return $this->data[$this->type]['time'] = $time;
    }
}
