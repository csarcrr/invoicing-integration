<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\OutputData;

interface ShouldGetInvoicePdf extends ShouldExecute, ShouldHavePayload
{
    public function getOutput(): OutputData;

    public function getUrl(): string;
}
