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

    public function address(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function city(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function postalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function country(?string $country): self
    {
        try {
            $data = (new ISO3166)->alpha2(strtolower($country));
        } catch (\Exception $e) {
            throw new InvalidCountryException;
        }

        $this->country = $country;

        return $this;
    }

    public function vat(?string $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function name(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function email(?string $email): self
    {

        Validator::make(['email' => $email], [

            'email' => 'required|email',

        ])->validate();

        $this->email = $email;

        return $this;
    }

    public function phone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function irsRetention(bool $irsRetention): self
    {
        $this->irsRetention = $irsRetention;

        return $this;
    }
}
