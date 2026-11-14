<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldFindWarehouse;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Warehouse\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Warehouse\Find as MoloniFind;

final class WarehouseAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function create(WarehouseData $warehouse): ShouldCreateWarehouse
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new MoloniCreate($warehouse),
        };
    }

    public function find(): ShouldFindWarehouse
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind,
        };
    }
}
