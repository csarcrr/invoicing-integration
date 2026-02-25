<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\TransportData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
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
