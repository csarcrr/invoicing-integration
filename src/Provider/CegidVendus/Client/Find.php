<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusClient;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends CegidVendusClient implements FindClient, ShouldHavePagination, ShouldHavePayload
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, mixed> */
    protected Collection $list;

    public function __construct(protected ?ClientData $client = null)
    {
        if (! $client) {
            $this->client = ClientData::from([]);
        }

        $this->payload = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->get('/clients', $this->getPayload());

        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request);
        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, mixed>
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

        $this->getClientAllowedProperties()->each(fn (mixed $item, string $key) => $this->payload->put($key, $item));

        $this->buildVat();
        $this->buildExternalReference();
        $this->buildStatus();

        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    private function buildVat(): void
    {
        (is_string($this->client->vat) || is_int($this->client->vat)) && $this->payload->put('fiscal_id', $this->client->vat);
    }

    private function buildExternalReference(): void
    {
        is_string($this->client->externalReference) && $this->payload->put('external_reference', $this->client->externalReference);
    }

    private function buildStatus(): void
    {
        is_string($this->client->status) && $this->payload->put('status', $this->client->status);
    }

    protected function updatePaginationDetails(Response $results): void
    {
        $this->totalPages((int) $results->header('X-Paginator-Pages') ?: 1);
    }

    /**
     * @param  array<string, mixed>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(function (array $item) {
            $data = [];
            // todo: improve this since the response fields might be similar to the dto ones
            ! empty($item['name']) && $data['name'] = $item['name'];

            return ClientData::from($data);
        })->values();
    }
}
