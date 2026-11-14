<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;
use function is_string;
use function throw_if;

class Delete extends Client implements ShouldDeleteClient
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(ClientData $client)
    {
        $this->data = $client;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Client);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): void
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Client could not be deleted.')
        );
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
        $this->url = 'customers/delete';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $this->payload->put('customer_id', $this->data->id);
    }
}
