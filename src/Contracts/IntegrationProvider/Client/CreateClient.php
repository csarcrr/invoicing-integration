<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

interface CreateClient
{
    public function name(): self;
    public function getName(): string;

    public function vat(): self;
    public function getVat(): string;

    public function address(): self;
    public function getAddress(): string;

    public function city(): self;
    public function getCity(): string;

    public function postalCode(): self;
    public function getPostalCode(): string;

    public function country(): self;
    public function getCountry(): string;

    public function email(): self;
    public function getEmail(): string;

    public function phone(): self;
    public function getPhone(): string;

    public function notes(): self;
    public function getNotes(): string;

    public function emailNotification(): self;
    public function getEmailNotification(): bool;

    public function defaultPayDue(): self;
    public function getDefaultPayDue(): int;

    public function irsRetention(): self;
    public function getIrsRetention(): bool;
}
