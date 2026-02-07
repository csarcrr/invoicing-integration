<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\ShouldCreate;

final class InvoiceAction
{
    public function __construct(
        protected ProviderConfigurationService $providerConfiguration
    ) {}

    public function create(): ShouldCreateInvoice
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new ShouldCreate($this->providerConfiguration->getConfig()),
        };
    }
}
