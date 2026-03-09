<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use Spatie\LaravelData\Data;

/**
 * Base class for Cegid Vendus item operations, holding item data and supported API response properties.
 */
#[AllowDynamicProperties]
class Item extends Base
{
    /**
     * Returns the current item DTO after an operation has been executed.
     * @returns ItemData
     */
    public function getItem(): Data
    {
        return $this->data;
    }
}
