<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Invoice;

class InvoiceTransportDetails
{
    protected ?string $type = null;
    protected array $data = [
        'origin' => [
            'address' => null,
            'city' => null,
            'postalCode' => null,
            'country' => null,
        ],
        'destination' => [
            'address' => null,
            'city' => null,
            'postalCode' => null,
            'country' => null,
        ]
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

    public function address(?string $address = null): ?string
    {
        if (!$address) {
            return $this->data[$this->type]['address'];
        }

        return $this->data[$this->type]['address'] = $address;
    }

    public function city(?string $city = null): ?string
    {
        if (!$city) {
            return $this->data[$this->type]['city'];
        }

        return $this->data[$this->type]['city'] = $city;
    }

    public function postalCode(?string $postalCode = null): ?string
    {
        if (!$postalCode) {
            return $this->data[$this->type]['postalCode'];
        }

        return $this->data[$this->type]['postalCode'] = $postalCode;
    }

    public function country(?string $country = null): ?string
    {
        if (!$country) {
            return $this->data[$this->type]['country'];
        }

        return $this->data[$this->type]['country'] = $country;
    }
}
