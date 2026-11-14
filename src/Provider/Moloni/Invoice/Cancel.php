<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;
use function is_int;
use function throw_if;

class Cancel extends Invoice implements ShouldCancelInvoice
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): void
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Invoice could not be cancelled.')
        );
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
        $this->url = 'documents/documentCancel';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $this->payload->put('document_id', $this->data->id);

        if (Properties::isValid($this->data->notes)) {
            $this->payload->put('observation', $this->data->notes);
        }
    }
}
