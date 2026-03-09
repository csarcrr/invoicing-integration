<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Spatie\LaravelData\Data;

/**
 * Base class for Cegid Vendus invoice operations, holding invoice data and supported API response properties.
 */
#[AllowDynamicProperties]
class Invoice extends Base
{
    /**
     * Returns the current invoice DTO after an operation has been executed.
     */
    public function getInvoice(): Data
    {
        return $this->data;
    }
}
