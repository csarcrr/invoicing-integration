<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldUpdateClient;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_string;
use function throw_if;

class Update extends Create implements ShouldUpdateClient
{
    public function execute(): self
    {
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $request = Http::provider()->patch('/clients/'.$this->data->id, $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->data = $this->toClientData($data);
        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        parent::getPayload();

        Properties::isValid($this->data->reference) && $this->payload->put('external_reference', $this->data->reference);
        Properties::isValid($this->data->status) && $this->payload->put('status', $this->data->status);

        return $this->payload;
    }
}
