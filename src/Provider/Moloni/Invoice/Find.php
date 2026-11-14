<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldFindInvoice;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Invoice implements ShouldFindInvoice
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, InvoiceData> */
    protected Collection $list;

    public function __construct(protected ?InvoiceData $invoice = null)
    {
        $this->data = $invoice ?? InvoiceData::from([]);
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);
    }

    public function execute(): self
    {
        $request = Http::provider()->post($this->endpoint(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, InvoiceData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildPagination();
        $this->buildClient();

        return $this->payload;
    }

    protected function endpoint(): string
    {
        return match ($this->data->type) {
            InvoiceType::Invoice => 'invoices/getAll',
            InvoiceType::InvoiceReceipt => 'invoiceReceipts/getAll',
            InvoiceType::InvoiceSimple => 'simplifiedInvoices/getAll',
            InvoiceType::Receipt => 'receipts/getAll',
            InvoiceType::Transport => 'billsOfLading/getAll',
            InvoiceType::CreditNote => 'creditNotes/getAll',
        };
    }

    protected function buildPagination(): void
    {
        $this->payload->put('qty', $this->perPage);
        $this->payload->put('offset', ($this->getCurrentPage() - 1) * $this->perPage);
    }

    protected function buildClient(): void
    {
        $client = $this->data->client;

        if (! ($client instanceof ClientData) || Properties::isNotValid($client->id)) {
            return;
        }

        $this->payload->put('customer_id', (int) $client->id);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(fn (array $item) => $this->toInvoiceData($item))->values();
    }
}
