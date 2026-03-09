<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ShouldCreateInvoice
{
    public function execute(): self;

    /**
     * @return InvoiceData
     */
    public function getInvoice(): Data;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
