<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\TaxExemption\ShouldFindTaxExemption;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Vendus\TaxExemption\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\TaxExemption\Find as MoloniFind;

final class TaxExemptionAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(): ShouldFindTaxExemption
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind,
        };
    }
}
