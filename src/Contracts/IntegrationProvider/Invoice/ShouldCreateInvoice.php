<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;

interface ShouldCreateInvoice
{
    public function execute(): self;

    public function getInvoice(): InvoiceData;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
