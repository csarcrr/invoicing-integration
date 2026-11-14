<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;
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
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Invoice);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): void
    {
        $response = Http::provider()->patch($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);
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

        $this->url = "documents/{$this->data->id}";
    }

    protected function buildPayload(): void
    {
        $this->payload->put('status', 'A');
    }
}
