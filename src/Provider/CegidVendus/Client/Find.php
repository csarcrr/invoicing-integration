<?php

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmail;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Find implements FindClient, ShouldHavePayload
{
    use HasPaginator;
    use HasEmail;

    protected Collection $payload;
    /**
     * @var \Illuminate\Support\Collection
     */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $this->buildPagination();
        $this->buildEmail();

        /* @phpstan-ignore-next-line */
        $request = Http::provider()->get('/clients', $this->getPayload());

        /* @phpstan-ignore-next-line */
        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request->json());
        $this->updateResults($request->json());

        return $this;
    }

    public function getList(): Collection
    {
        return $this->list;
    }

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

    protected function updatePaginationDetails(array $results): void
    {
    }

    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(function (array $item) {
            $client = ClientData::getFacadeRoot();

            !empty($item['name']) && $client->name($item['name']);

            return $client;
        });
    }
}
