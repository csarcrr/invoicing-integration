<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;

interface ShouldCancelInvoice extends ShouldHavePayload
{
    public function execute(): void;

    public function getUrl(): string;
}
