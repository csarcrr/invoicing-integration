<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldUpdateClient;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetCountryIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetLanguageIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingClientDataException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_string;
use function throw_if;

class Update extends Create implements ShouldUpdateClient
{
    public function execute(): self
    {
        $response = Http::provider()->post('customers/update', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws MissingClientDataException
     * @throws CouldNotGetCountryIdException
     * @throws CouldNotGetLanguageIdException
     */
    public function getPayload(): Collection
    {
        throw_if(! is_string($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $this->payload->put('customer_id', $this->data->id);

        return parent::getPayload();
    }
}
