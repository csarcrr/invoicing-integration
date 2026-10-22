<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldDeleteItemStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldGetItemStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock\Delete;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock\Get;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock\Move;

final class ItemStockAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function get(ItemData $item): ShouldGetItemStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Get($item),
        };
    }

    public function move(StockMovementData $movement): ShouldMoveItemStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Move($movement),
        };
    }

    public function delete(StockMovementData $movement): ShouldDeleteItemStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Delete($movement),
        };
    }
}
