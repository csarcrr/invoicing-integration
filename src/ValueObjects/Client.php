<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use Illuminate\Support\Facades\Validator;
use League\ISO3166\ISO3166;

class Client
{
    protected ?string $address = null;

    protected ?string $city = null;

    protected ?string $postalCode = null;

    protected ?string $country = null;

    protected ?string $email = null;

    protected ?string $phone = null;

    protected ?bool $irsRetention = null;

    public function __construct(protected ?string $vat = null, protected ?string $name = null) {}

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getIrsRetention(): ?bool
    {
        return $this->irsRetention;
    }

    public function address(?string $address): void
    {
        $this->address = $address;
    }

    public function city(?string $city): void
    {
        $this->city = $city;
    }

    public function postalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function country(?string $country): void
    {
        try {
            $data = (new ISO3166)->alpha2(strtolower($country));
        } catch (\Exception $e) {
            throw new InvalidCountryException;
        }

        $this->country = $country;
    }

    public function vat(?string $vat): void
    {
        $this->vat = $vat;
    }

    public function name(?string $name): void
    {
        $this->name = $name;
    }

    public function email(?string $email): void
    {

        Validator::make(['email' => $email], [

            'email' => 'required|email',

        ])->validate();

        $this->email = $email;
    }

    public function phone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function irsRetention(bool $irsRetention): void
    {
        $this->irsRetention = $irsRetention;
    }
}
