<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Create;

/**
 * Orchestrates invoice operations by routing them to the correct provider implementation.
 */
final class InvoiceAction
{
    public function __construct(
        protected ProviderConfigurationService $providerConfiguration
    ) {}

    /**
     * Returns a provider-specific implementation to create an invoice.
     */
    public function create(InvoiceData $invoice): ShouldCreateInvoice
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => (new Create($invoice))->config($this->providerConfiguration->getConfig()),
        };
    }
}
