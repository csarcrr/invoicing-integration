<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFetchClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldUpdateClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Create;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Delete;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Fetch;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Find;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Update;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client\Delete as MoloniDelete;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client\Fetch as MoloniFetch;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client\Find as MoloniFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Client\Update as MoloniUpdate;

/**
 * Orchestrates client operations by routing them to the correct provider implementation.
 */
final class ClientAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    /**
     * Returns a provider-specific implementation to create a client.
     */
    public function create(ClientData $client): ShouldCreateClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Create($client),
            Provider::MOLONI => new MoloniCreate($client),
        };
    }

    /**
     * Returns a provider-specific implementation to update a client.
     */
    public function update(ClientData $client): ShouldUpdateClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Update($client),
            Provider::MOLONI => new MoloniUpdate($client),
        };
    }

    /**
     * Returns a provider-specific implementation to delete a client by ID.
     */
    public function delete(ClientData $client): ShouldDeleteClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Delete($client),
            Provider::MOLONI => new MoloniDelete($client),
        };
    }

    /**
     * Returns a provider-specific implementation to retrieve a single client by ID.
     */
    public function fetch(ClientData $client): ShouldFetchClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Fetch($client),
            Provider::MOLONI => new MoloniFetch($client),
        };
    }

    /**
     * Returns a provider-specific implementation to search for clients.
     */
    public function find(?ClientData $client = null): ShouldFindClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Find($client),
            Provider::MOLONI => new MoloniFind($client),
        };
    }
}
