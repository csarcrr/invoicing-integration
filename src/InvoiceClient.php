<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use Illuminate\Support\Facades\Validator;

class InvoiceClient
{
    protected ?string $address = null;

    protected ?string $city = null;

    protected ?string $postalCode = null;

    protected ?string $country = null;

    protected ?string $email = null;

    protected ?string $phone = null;

    public ?string $vat = null;

    public ?string $name = null;

    public function __construct(?string $vat = null, ?string $name = null)
    {
        $this->vat = $vat;
        $this->name = $name;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function postalCode(): ?string
    {
        return $this->postalCode;
    }

    public function country(): ?string
    {
        return $this->country;
    }

    public function vat(): ?string
    {
        return $this->vat;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function setVat(?string $vat): void
    {
        $this->vat = $vat;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(?string $email): void
    {

        Validator::make(['email' => $email], [

            'email' => 'required|email',

        ])->validate();

        $this->email = $email;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }
}
