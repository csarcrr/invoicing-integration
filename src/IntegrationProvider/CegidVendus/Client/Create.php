<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Traits\Client\HasAddress;
use CsarCrr\InvoicingIntegration\Traits\Client\HasCity;
use CsarCrr\InvoicingIntegration\Traits\Client\HasCountry;
use CsarCrr\InvoicingIntegration\Traits\Client\HasDefaultPayDue;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmail;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmailNotification;
use CsarCrr\InvoicingIntegration\Traits\Client\HasIrsRetention;
use CsarCrr\InvoicingIntegration\Traits\Client\HasName;
use CsarCrr\InvoicingIntegration\Traits\Client\HasNotes;
use CsarCrr\InvoicingIntegration\Traits\Client\HasPhone;
use CsarCrr\InvoicingIntegration\Traits\Client\HasPostalCode;
use CsarCrr\InvoicingIntegration\Traits\Client\HasVat;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use Illuminate\Support\Collection;

class Create implements CreateClient, ShouldHaveConfig, ShouldHavePayload
{
    use HasAddress;
    use HasCity;
    use HasConfig;
    use HasCountry;
    use HasDefaultPayDue;
    use HasEmail;
    use HasEmailNotification;
    use HasIrsRetention;
    use HasName;
    use HasNotes;
    use HasPhone;
    use HasPostalCode;
    use HasVat;

    protected Collection $payload;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config($config);
        $this->payload = collect();
    }

    public function execute(): Client
    {
        return new Client;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildName();
        $this->buildEmail();
        $this->buildCompleteAddress();
        $this->buildVat();
        $this->buildNotes();
        $this->buildIrsRetention();
        $this->buildEmailNotification();
        $this->buildContacts();
        $this->buildDefaultPayDue();

        return $this->payload;
    }

    protected function buildName(): void
    {
        $this->getName() && $this->payload->put('name', $this->getName());
    }

    protected function buildEmail(): void
    {
        $this->getEmail() && $this->payload->put('email', $this->getEmail());
    }

    protected function buildCompleteAddress(): void
    {
        $this->getAddress() && $this->payload->put('address', $this->getAddress());
        $this->getCity() && $this->payload->put('city', $this->getCity());
        $this->getPostalCode() && $this->payload->put('postalcode', $this->getPostalCode());
        $this->getCountry() && $this->payload->put('country', $this->getCountry());
    }

    protected function buildVat(): void
    {
        $this->getVat() && $this->payload->put('fiscal_id', $this->getVat());
    }

    protected function buildNotes(): void
    {
        $this->getNotes() && $this->payload->put('notes', $this->getNotes());
    }

    protected function buildIrsRetention(): void
    {
        $this->getIrsRetention() ? $this->payload->put('irs_retention', "yes") : $this->payload->put('irs_retention', "no");
    }

    protected function buildEmailNotification(): void
    {
        $this->getEmailNotification() ? $this->payload->put('email_notification', "yes") : $this->payload->put('email_notification', "no");
    }

    protected function buildContacts(): void
    {
        $this->getPhone() && $this->payload->put('phone', $this->getPhone());
     }

    protected function buildDefaultPayDue(): void
    {
        $this->getDefaultPayDue() && $this->payload->put('default_pay_due', $this->getDefaultPayDue());
    }
}
