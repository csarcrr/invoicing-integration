<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use Illuminate\Support\Collection;

interface CreateClient
{
    public function execute () : Client;
    
    public function name(string $name): self;

    public function getName(): string;

    public function vat(string|int $vat): self;

    public function getVat(): string|int;

    public function address(string $address): self;

    public function getAddress(): string;

    public function city(string $city): self;

    public function getCity(): string;

    public function postalCode(string $postalCode): self;

    public function getPostalCode(): string;

    public function country(string $country): self;

    public function getCountry(): string;

    public function email(string $email): self;

    public function getEmail(): string;

    public function phone(string $phone): self;

    public function getPhone(): string;

    public function notes(string $notes): self;

    public function getNotes(): string;

    public function emailNotification(bool $emailNotification): self;

    public function getEmailNotification(): bool;

    public function defaultPayDue(int $defaultPayDue): self;

    public function getDefaultPayDue(): int;

    public function irsRetention(bool $irsRetention): self;

    public function getIrsRetention(): bool;

    public function getPayload(): Collection;
}
