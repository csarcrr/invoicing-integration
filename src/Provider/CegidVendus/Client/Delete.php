<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_string;
use function throw_if;

class Delete implements ShouldDeleteClient
{
    public function __construct(protected ClientData $client) {}

    public function execute(): void
    {
        throw_if(! is_string($this->client->id), InvalidArgumentException::class, 'Client ID is required.');

        $response = Http::provider()->delete("clients/{$this->client->id}");

        Http::handleUnwantedFailures($response);
    }
}
