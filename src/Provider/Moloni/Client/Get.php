<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldGetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function collect;
use function is_string;
use function throw_if;

class Get extends Client implements ShouldGetClient
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ClientData $client)
    {
        $this->data = $client;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Client);
    }

    public function execute(): self
    {
        $request = Http::provider()->post('customers/getOne', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->fillProperties($data);
        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildId();

        return $this->payload;
    }

    protected function buildId(): void
    {
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $this->payload->put('customer_id', $this->data->id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillProperties(array $data): void
    {
        $this->data = ClientData::from(collect([
            'id' => $this->data->id,
            'name' => $data['name'] ?? null,
            'vat' => $data['vat'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['zip_code'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'country' => Str::upper($data['country']['iso_3166_1'] ?? ''),
        ])->filter()->toArray());
    }
}
