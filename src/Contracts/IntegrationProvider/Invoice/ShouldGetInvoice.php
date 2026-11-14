<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

interface ShouldGetInvoice extends ShouldExecute
{
    public function getInvoice(): InvoiceData;
}
