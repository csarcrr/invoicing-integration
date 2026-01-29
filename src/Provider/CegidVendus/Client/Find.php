<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusClient;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Find extends CegidVendusClient implements FindClient, ShouldHavePagination, ShouldHavePayload
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, mixed> */
    protected Collection $list;

    public function __construct(protected ?ClientData $client = null)
    {
        $this->client = ClientData::from([]);
        $this->payload = collect();
    }

    public function execute(): self
    {
        $this->buildPagination();
        $this->buildEmail();

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
        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    private function buildEmail(): void
    {
        ! ($this->client->email instanceof Optional) && $this->payload->put('email', $this->client->email);
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
