<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Unit;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldCreateUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;
use function is_null;
use function throw_if;

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

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Unit could not be created.')
        );

        $this->data = UnitData::make([
            'id' => (int) $response->json('unit_id'),
            'name' => $this->data->name,
            'shortName' => $this->data->shortName,
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
        $this->url = 'measurementUnits/insert';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('name', $this->data->name);

        if (! is_null($this->data->shortName)) {
            $this->payload->put('short_name', $this->data->shortName);
        }
    }
}
