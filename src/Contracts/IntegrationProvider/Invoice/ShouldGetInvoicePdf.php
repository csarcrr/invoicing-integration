<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\OutputData;

interface ShouldGetInvoicePdf extends ShouldExecute
{
    public function getOutput(): OutputData;
}
