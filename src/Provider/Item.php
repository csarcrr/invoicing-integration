<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;

/**
 * Base class for Cegid Vendus item operations, holding item data and supported API response properties.
 */
#[AllowDynamicProperties]
class Item extends Base
{
    protected ?ItemData $item = null;

    /**
     * Returns the current item DTO after an operation has been executed.
     */
    public function getItem(): ItemData
    {
        return $this->item;
    }
}
