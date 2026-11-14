<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

class Cancel implements ShouldCancelInvoice
{
    public function __construct(protected InvoiceData $invoice) {}

    public function execute(): void
    {
        throw_if(! is_int($this->invoice->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $response = Http::provider()->patch("documents/{$this->invoice->id}", ['status' => 'A']);

        Http::handleUnwantedFailures($response);
    }
}
