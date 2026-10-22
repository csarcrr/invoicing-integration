<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\ProductStockAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldDeleteProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldGetProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldMoveProductStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldGetProductStock get(ItemData $item)
 * @method static ShouldMoveProductStock move(StockMovementData $movement)
 * @method static ShouldDeleteProductStock delete(StockMovementData $movement)
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\ProductStockAction
 */
class ProductStock extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ProductStockAction::class;
    }
}
