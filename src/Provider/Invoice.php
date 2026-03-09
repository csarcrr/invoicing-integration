<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

/**
 * Base class for Cegid Vendus invoice operations, holding invoice data and supported API response properties.
 */
#[AllowDynamicProperties]
class Invoice extends Base
{
    protected InvoiceData $invoice;

    /**
     * Returns the current invoice DTO after an operation has been executed.
     */
    public function getInvoice(): InvoiceData
    {
        return $this->invoice;
    }
}
