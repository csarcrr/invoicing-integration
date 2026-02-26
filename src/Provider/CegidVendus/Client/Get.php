<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusClient;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

class Get extends CegidVendusClient implements GetClient
{
    public function __construct(protected ?ClientData $client) {}

    /**
     * @throws InvalidArgumentException|\Throwable
     */
    public function execute(): self
    {
        throw_if(! is_int($this->client->id), InvalidArgumentException::class, 'Client ID is required.');

        $request = Http::provider()->get('/clients/'.$this->client->id);

        Http::handleUnwantedFailures($request);

        $this->updateClientData($request->json());

        return $this;
    }
}
