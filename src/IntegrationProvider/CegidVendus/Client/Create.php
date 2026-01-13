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

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config)
    {
        $this->config($config);
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

        return collect();
    }

    protected function buildName(): void {}

    protected function buildEmail(): void {}

    protected function buildCompleteAddress(): void {}

    protected function buildVat(): void {}

    protected function buildNotes(): void {}

    protected function buildIrsRetention(): void {}

    protected function buildEmailNotification(): void {}

    protected function buildContacts(): void {}

    protected function buildDefaultPayDue(): void {}
}
