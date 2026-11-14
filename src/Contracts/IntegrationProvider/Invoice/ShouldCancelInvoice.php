<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

interface ShouldCancelInvoice
{
    public function execute(): void;
}
