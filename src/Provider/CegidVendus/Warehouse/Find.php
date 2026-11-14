<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldFindWarehouse;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Warehouse implements ShouldFindWarehouse
{
    use DetectsNoData;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, WarehouseData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->list = collect();

        $this->buildUrl();
    }

    public function execute(): self
    {
        $response = Http::provider()->get($this->getUrl(), $this->getPayload()->toArray());

        if ($this->hasNoData($response)) {
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $results */
        $results = $response->json();

        $this->list = collect($results)->map(fn (array $warehouse) => $this->toWarehouseData($warehouse))->values();

        return $this;
    }

    /**
     * @return Collection<int, WarehouseData>
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

    protected function buildUrl(): void
    {
        $this->url = 'stores';
    }
}
