<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\ItemStock\Move as VendusMove;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock\Move;

final class ItemStockAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function move(StockMovementData $movement): ShouldMoveItemStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusMove($movement),
            Provider::MOLONI => new Move($movement),
        };
    }
}
