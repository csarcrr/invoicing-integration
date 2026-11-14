<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends Warehouse implements ShouldCreateWarehouse
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(WarehouseData $warehouse)
    {
        $this->data = $warehouse;
        $this->payload = collect();

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = WarehouseData::make(
            ['id' => (int) $response->json('id')] + $this->data->toArray()
        )->additional(collect($response->json())->except(['id', 'title'])->toArray());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    public function getWarehouse(): WarehouseData
    {
        return $this->data;
    }

    protected function buildUrl(): void
    {
        $this->url = 'stores';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('title', $this->data->name);

        if (! is_null($this->data->address)) {
            $this->payload->put('address', $this->data->address);
        }

        if (! is_null($this->data->city)) {
            $this->payload->put('local', $this->data->city);
        }

        if (! is_null($this->data->postalCode)) {
            $this->payload->put('postalcode', $this->data->postalCode);
        }
    }
}
