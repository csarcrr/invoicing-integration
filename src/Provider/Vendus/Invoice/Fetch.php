<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Vendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldFetchInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;
use function is_int;
use function throw_if;

class Fetch extends Invoice implements ShouldFetchInvoice
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::VENDUS->supportedProperties(Property::Invoice);

        $this->buildUrl();
    }

    public function get(): InvoiceData
    {
        return $this->data;
    }

    public function execute(): static
    {
        $response = Http::provider()->get($this->getUrl());

        Http::handleUnwantedFailures($response);

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
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $this->url = 'documents/'.$this->data->id;
    }
}
