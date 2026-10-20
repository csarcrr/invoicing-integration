<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\ItemAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateItem create(ItemData $item)
 * @method static ShouldGetItem get(ItemData $item)
 * @method static ShouldFindItem find(?ItemData $item = null)
 *
 * @see ItemAction
 */
class Item extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ItemAction::class;
    }
}
