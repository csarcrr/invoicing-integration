<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;
use function is_int;
use function throw_if;

class Get extends Invoice implements ShouldGetInvoice
{
    use DetectsNoData;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if($this->hasNoData($response), RequestFailedException::class, 'Invoice not found.');

        $this->data = $this->toInvoiceData($response->json());

        return $this;
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
        $this->url = 'documents/getOne';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $this->payload->put('document_id', $this->data->id);
    }
}
