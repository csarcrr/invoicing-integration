<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldFindInvoice;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Invoice implements ShouldFindInvoice
{
    use DetectsNoData;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, InvoiceData> */
    protected Collection $list;

    public function __construct(protected ?InvoiceData $invoice = null)
    {
        $this->data = $invoice ?? InvoiceData::from([]);
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Invoice);

        $this->buildUrl();
    }

    public function execute(): self
    {
        $request = Http::provider()->get($this->getUrl(), $this->getPayload());

        if ($this->hasNoData($request)) {
            $this->updatePaginationDetails($request);
            $this->updateResults([]);

            return $this;
        }

        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request);
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
        $this->buildType();
        $this->buildClient();

        return $this->payload;
    }

    protected function buildUrl(): void
    {
        $this->url = '/documents';
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    protected function buildType(): void
    {
        $this->payload->put('type', $this->data->type->value);
    }

    protected function buildClient(): void
    {
        $client = $this->data->client;

        if (! ($client instanceof ClientData) || Properties::isNotValid($client->id)) {
            return;
        }

        $this->payload->put('client_id', $client->id);
    }

    protected function updatePaginationDetails(Response $results): void
    {
        $this->totalPages((int) $results->header('X-Paginator-Pages') ?: 1);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(fn (array $item) => $this->toInvoiceData($item))->values();
    }
}
