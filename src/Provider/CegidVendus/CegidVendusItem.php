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
        'id', 'name', 'email', 'postalcode', 'country', 'city', 'address', 'phone', 'notes', 'default_pay_due', 'fiscal_id', 'send_email', 'irs_retention', 'date',
    ];

    public function getItem(): ItemData
    {
        return $this->item;
    }
}
