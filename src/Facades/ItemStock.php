<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\ItemStockAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldMoveItemStock move(StockMovementData $movement)
 *
 * @see ItemStockAction
 */
class ItemStock extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ItemStockAction::class;
    }
}
