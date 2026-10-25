<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldGetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_string;
use function throw_if;

class Get extends Client implements ShouldGetClient
{
    public function __construct(protected ?ClientData $client)
    {
        $this->data = $client;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Client);
    }

    /**
     * @throws InvalidArgumentException|\Throwable
     */
    public function execute(): self
    {
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $request = Http::provider()->get('/clients/'.$this->data->id);

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->data = $this->toClientData($data);
        $this->fillAdditionalProperties($data);

        return $this;
    }
}
