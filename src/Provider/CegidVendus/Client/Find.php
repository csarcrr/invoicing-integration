<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Client implements ShouldFindClient
{
    use DetectsNoData;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, ClientData> */
    protected Collection $list;

    public function __construct(protected ?ClientData $client = null)
    {
        $this->data = $client ?? ClientData::from([]);
        $this->payload = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->get('/clients', $this->getPayload());

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
     * @return Collection<int, ClientData>
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
        $this->buildVat();
        $this->buildName();
        $this->buildEmail();
        $this->buildExternalReference();
        $this->buildStatus();
        $this->buildDate();

        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    private function buildVat(): void
    {
        Properties::isValid($this->data->vat) && $this->payload->put('fiscal_id', $this->data->vat);
    }

    private function buildName(): void
    {
        Properties::isValid($this->data->name) && $this->payload->put('name', $this->data->name);
    }

    private function buildEmail(): void
    {
        Properties::isValid($this->data->email) && $this->payload->put('email', $this->data->email);
    }

    private function buildExternalReference(): void
    {
        Properties::isValid($this->data->externalReference) && $this->payload->put('external_reference', $this->data->externalReference);
    }

    private function buildStatus(): void
    {
        Properties::isValid($this->data->status) && $this->payload->put('status', $this->data->status);
    }

    private function buildDate(): void
    {
        $this->data->date instanceof Carbon && $this->payload->put('date', $this->data->date->format('Y-m-d'));
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
        $this->list = collect($results)->map(fn (array $item) => $this->toClientData($item))->values();
    }
}
