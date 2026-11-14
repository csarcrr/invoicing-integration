<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldFindWarehouse;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Warehouse\Find as MoloniFind;

final class WarehouseAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(): ShouldFindWarehouse
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind,
        };
    }
}
