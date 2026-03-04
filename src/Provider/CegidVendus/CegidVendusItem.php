<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\Base;

#[AllowDynamicProperties]
class CegidVendusItem extends Base
{
    protected ?ItemData $item = null;

    /**
     * @var list<string>
     */
    protected array $supportedProperties = [
        'title', 'reference', 'qty', 'gross_price', 'discount_percent', 'discount_amount',
        'note', 'type_id', 'tax_id', 'tax_exemption', 'tax_exemption_law',
    ];

    public function getItem(): ItemData
    {
        return $this->item;
    }
}
