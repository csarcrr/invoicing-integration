<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\ItemAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateItem create(ItemData $item)
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\ItemAction
 */
class Item extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ItemAction::class;
    }
}
