<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldCreateUnit;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Unit\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Unit\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Unit\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Unit\Find as MoloniFind;

final class UnitAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(): ShouldFindUnit
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind,
        };
    }

    public function create(UnitData $unit): ShouldCreateUnit
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => new VendusCreate($unit),
            Provider::MOLONI => new MoloniCreate($unit),
        };
    }
}
