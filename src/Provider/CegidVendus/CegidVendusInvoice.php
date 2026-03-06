<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Provider\Base;

/**
 * Base class for Cegid Vendus invoice operations, holding invoice data and supported API response properties.
 */
#[AllowDynamicProperties]
class CegidVendusInvoice extends Base
{
    protected InvoiceData $invoice;

    /**
     * @var list<string>
     */
    protected array $supportedProperties = ['id', 'type', 'number', 'amount_gross', 'amount_net', 'atcud', 'output'];

    /**
     * Returns the current invoice DTO after an operation has been executed.
     */
    public function getInvoice(): InvoiceData
    {
        return $this->invoice;
    }
}
