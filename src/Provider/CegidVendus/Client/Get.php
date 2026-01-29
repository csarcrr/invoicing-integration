<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Contracts\ValueObjects\HasClientData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusClient;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use Spatie\LaravelData\Data;
use function throw_if;

class Get extends CegidVendusClient implements GetClient
{
    public function __construct(protected ?ClientData $client) {}

    /**
     * @throws InvalidArgumentException|\Throwable
     */
    public function execute(): ClientData
    {
        $client = $this->client->toArray();

        throw_if(empty($client['id']), InvalidArgumentException::class, 'Client ID is required.');

        $request = Http::provider()->get('/clients/'.$client['id']);

        Http::handleUnwantedFailures($request);

        $this->updateClientData($request->json());

        return $this->client;
    }
}
