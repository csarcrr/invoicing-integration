<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\WarehouseAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldFindWarehouse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindWarehouse find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\WarehouseAction
 */
class Warehouse extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return WarehouseAction::class;
    }
}
