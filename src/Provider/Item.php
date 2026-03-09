<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;

/**
 * Base class for Cegid Vendus item operations, holding item data and supported API response properties.
 *
 * @extends Base<ItemData>
 */
#[AllowDynamicProperties]
class Item extends Base
{
    /**
     * Returns the current item DTO after an operation has been executed.
     */
    public function getItem(): ItemData
    {
        return $this->data;
    }
}
