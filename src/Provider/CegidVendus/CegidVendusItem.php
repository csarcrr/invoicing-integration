<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\Base;
use Exception;
use Illuminate\Support\Collection;

use function collect;
use function in_array;
use function throw_if;

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
