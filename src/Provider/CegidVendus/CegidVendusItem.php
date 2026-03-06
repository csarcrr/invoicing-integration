<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\Base;

/**
 * Base class for Cegid Vendus item operations, holding item data and supported API response properties.
 */
#[AllowDynamicProperties]
class CegidVendusItem extends Base
{
    protected ?ItemData $item = null;

    /**
     * @var list<string>
     */
    protected array $supportedProperties = [
        'title', 'reference', 'qty', 'gross_price', 'description', 'tax_id', 'tax_exemption', 'tax_exemption_law', 'unit_id',
    ];

    /**
     * Returns the current item DTO after an operation has been executed.
     */
    public function getItem(): ItemData
    {
        return $this->item;
    }
}
