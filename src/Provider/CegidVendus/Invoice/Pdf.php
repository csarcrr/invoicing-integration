<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function base64_encode;
use function is_int;
use function throw_if;

class Pdf implements ShouldGetInvoicePdf
{
    protected OutputData $output;

    public function __construct(protected InvoiceData $invoice) {}

    public function execute(): self
    {
        throw_if(! is_int($this->invoice->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $response = Http::provider()->get("documents/{$this->invoice->id}.pdf");

        Http::handleUnwantedFailures($response);

        $this->output = OutputData::make([
            'format' => OutputFormat::PDF_BASE64->value,
            'content' => base64_encode($response->body()),
        ]);

        return $this;
    }

    public function getOutput(): OutputData
    {
        return $this->output;
    }
}
