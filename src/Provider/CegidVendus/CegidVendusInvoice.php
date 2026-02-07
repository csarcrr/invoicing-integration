<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Provider\Base;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use Exception;
use Illuminate\Support\Collection;

use function collect;
use function in_array;
use function throw_if;

#[AllowDynamicProperties]
class CegidVendusInvoice extends Base
{
    protected InvoiceData $invoice;

    /**
     * @var list<string>
     */
    protected array $supportedProperties = ['id', 'type', 'number', 'amount_gross', 'amount_net', 'atcud', 'output'];

    public function getInvoice(): InvoiceData
    {
        return $this->invoice;
    }
}
