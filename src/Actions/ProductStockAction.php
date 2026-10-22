<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldDeleteProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldGetProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldMoveProductStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock\Delete;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock\Get;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock\Move;

final class ProductStockAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function get(ItemData $item): ShouldGetProductStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Get($item),
        };
    }

    public function move(StockMovementData $movement): ShouldMoveProductStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Move($movement),
        };
    }

    public function delete(StockMovementData $movement): ShouldDeleteProductStock
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Delete($movement),
        };
    }
}
