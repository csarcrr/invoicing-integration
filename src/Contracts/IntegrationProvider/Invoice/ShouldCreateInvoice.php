<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ShouldCreateInvoice extends ShouldHaveConfig, ShouldHavePayload
{

    /**
     * @return InvoiceData
     */
    public function getInvoice(): Data;
}
