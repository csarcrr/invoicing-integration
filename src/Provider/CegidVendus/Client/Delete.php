<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;
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
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Client);

        $this->buildUrl();
    }

    public function execute(): void
    {
        $response = Http::provider()->delete($this->getUrl());

        Http::handleUnwantedFailures($response);
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
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $this->url = "clients/{$this->data->id}";
    }
}
