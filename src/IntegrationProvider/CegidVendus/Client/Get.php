<?php

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Get implements GetClient
{

    public function __construct(protected ClientData $client)
    {

    }

    public function execute(): ClientData
    {
        $request = Http::provider()->get('/clients/'.$this->client->getId());
        Http::handleUnwantedFailures($request);

        return new ClientData();
    }
}
