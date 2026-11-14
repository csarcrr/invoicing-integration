<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;

interface ShouldFindInvoice extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
{
    public function execute(): self;

    /**
     * @return Collection<int, InvoiceData>
     */
    public function getList(): Collection;

    public function getUrl(): string;
}
