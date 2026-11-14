<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Warehouse;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
use CsarCrr\InvoicingIntegration\Data\CountryData;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Country\Get as GetCountry;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;
use function throw_if;

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
        $this->buildCountry();

        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Warehouse could not be created.')
        );

        $this->data = WarehouseData::make([
            'id' => (int) $response->json('warehouse_id'),
        ] + $this->data->toArray());

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
        $this->url = 'warehouses/insert';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('title', $this->data->name);

        if (! is_null($this->data->code)) {
            $this->payload->put('code', $this->data->code);
        }

        if (! is_null($this->data->address)) {
            $this->payload->put('address', $this->data->address);
        }

        if (! is_null($this->data->city)) {
            $this->payload->put('city', $this->data->city);
        }

        if (! is_null($this->data->postalCode)) {
            $this->payload->put('zip_code', $this->data->postalCode);
        }

        if (! is_null($this->data->isDefault)) {
            $this->payload->put('is_default', (int) $this->data->isDefault);
        }
    }

    protected function buildCountry(): void
    {
        if (is_null($this->data->country)) {
            return;
        }

        $country = (new GetCountry(CountryData::from(['iso' => $this->data->country])))->execute()->getCountry();

        $this->payload->put('country_id', $country->id);
    }
}
