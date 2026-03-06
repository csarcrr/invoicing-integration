<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Create;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Find;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Get;

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
    public function create(ClientData $client): CreateClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Create($client),
        };
    }

    /**
     * Returns a provider-specific implementation to retrieve a single client by ID.
     */
    public function get(ClientData $client): GetClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Get($client),
        };
    }

    /**
     * Returns a provider-specific implementation to search for clients.
     */
    public function find(?ClientData $client = null): FindClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Find($client),
        };
    }
}
