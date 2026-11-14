<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function base64_encode;
use function collect;
use function is_int;
use function throw_if;

class Pdf extends Invoice implements ShouldGetInvoicePdf
{
    protected OutputData $output;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Invoice);

        $this->buildUrl();
    }

    public function execute(): self
    {
        $response = Http::provider()->get($this->getUrl());

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

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    protected function buildUrl(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $this->url = "documents/{$this->data->id}.pdf";
    }
}
