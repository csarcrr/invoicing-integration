<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
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

        $response = Http::provider()->post('customers/delete', ['customer_id' => $this->client->id]);

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Client could not be deleted.')
        );
    }
}
