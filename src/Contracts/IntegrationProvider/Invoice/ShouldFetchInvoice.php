<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

interface ShouldFetchInvoice extends ShouldExecute, ShouldHavePayload
{
    public function get(): InvoiceData;

    public function getUrl(): string;
}
