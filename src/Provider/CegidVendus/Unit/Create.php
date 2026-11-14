<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Unit;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldCreateUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends Unit implements ShouldCreateUnit
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(UnitData $unit)
    {
        $this->data = $unit;
        $this->payload = collect();

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = UnitData::make([
            'id' => (int) $response->json('id'),
            'name' => $response->json('title'),
        ]);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    public function getUnit(): UnitData
    {
        return $this->data;
    }

    protected function buildUrl(): void
    {
        $this->url = 'products/units';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('title', $this->data->name);
    }
}
