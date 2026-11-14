<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

interface ShouldGetInvoice extends ShouldExecute, ShouldHavePayload
{
    public function execute(): self;

    public function getInvoice(): InvoiceData;

    public function getUrl(): string;
}
