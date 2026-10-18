<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Client;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function collect;

class Find extends Client implements ShouldFindClient
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, ClientData> */
    protected Collection $list;

    public function __construct(protected ?ClientData $client = null)
    {
        $this->data = $client ?? ClientData::from([]);
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Client);
    }

    public function execute(): self
    {
        $endpoint = match ($this->searchFilter()->keys()->first()) {
            'vat' => 'customers/getByVat',
            'email' => 'customers/getByEmail',
            'number' => 'customers/getByNumber',
            'name' => 'customers/getByName',
            default => 'customers/getAll',
        };

        $request = Http::provider()->post($endpoint, $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

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
        $this->buildSearch();
        $this->buildPagination();

        return $this->payload;
    }

    /**
     * @return Collection<string, mixed>
     */
    protected function searchFilter(): Collection
    {
        return collect([
            'vat' => $this->data->vat,
            'email' => $this->data->email,
            'number' => $this->data->id,
            'name' => $this->data->name,
        ])->filter(fn (mixed $value) => Properties::isValid($value))->take(1);
    }

    protected function buildSearch(): void
    {
        $this->searchFilter()->each(fn (mixed $value, string $key) => $this->payload->put($key, $value));
    }

    protected function buildPagination(): void
    {
        $this->payload->put('qty', $this->perPage);
        $this->payload->put('offset', ($this->getCurrentPage() - 1) * $this->perPage);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(fn (array $item) => ClientData::from(collect([
            'id' => isset($item['customer_id']) ? (string) $item['customer_id'] : null,
            'name' => $item['name'] ?? null,
            'vat' => $item['vat'] ?? null,
            'address' => $item['address'] ?? null,
            'city' => $item['city'] ?? null,
            'postal_code' => $item['zip_code'] ?? null,
            'email' => $item['email'] ?? null,
            'phone' => $item['phone'] ?? null,
            'notes' => $item['notes'] ?? null,
            'country' => Str::upper($item['country']['iso_3166_1'] ?? ''),
        ])->filter()->toArray()))->values();
    }
}
