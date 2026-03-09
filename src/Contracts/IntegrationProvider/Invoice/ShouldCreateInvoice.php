<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

interface ShouldCreateInvoice extends ShouldHaveConfig, ShouldHavePayload
{
    public function getInvoice(): InvoiceData;
}
