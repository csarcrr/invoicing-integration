<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmail;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Find implements FindClient, ShouldHavePagination, ShouldHavePayload
{
    use HasEmail;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, mixed> */
    protected Collection $list;

    public function __construct()
    {
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
        $this->email && $this->payload->put('email', $this->email);
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
            $client = app(ClientData::class);

            ! empty($item['name']) && $client->name($item['name']);

            return $client;
        })->values();
    }
}
