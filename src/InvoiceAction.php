<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Services\ProviderConfigurationService;

final class InvoiceAction
{
    public function __construct(
        protected ProviderConfigurationService $providerConfiguration
    ) {}

    public function create(): CreateInvoice
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new Create($this->providerConfiguration->getConfig()),
        };
    }
}
