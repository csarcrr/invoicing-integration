<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Cancel;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Get;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Pdf;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice\Cancel as MoloniCancel;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice\Get as MoloniGet;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice\Pdf as MoloniPdf;

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
            Provider::MOLONI => (new MoloniCreate($invoice))->config($this->providerConfiguration->getConfig()),
        };
    }

    public function get(InvoiceData $invoice): ShouldGetInvoice
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new Get($invoice),
            Provider::MOLONI => new MoloniGet($invoice),
        };
    }

    public function pdf(InvoiceData $invoice): ShouldGetInvoicePdf
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new Pdf($invoice),
            Provider::MOLONI => new MoloniPdf($invoice),
        };
    }

    public function cancel(InvoiceData $invoice): ShouldCancelInvoice
    {
        return match ($this->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new Cancel($invoice),
            Provider::MOLONI => new MoloniCancel($invoice),
        };
    }
}
