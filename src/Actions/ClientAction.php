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

final class ClientAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function create(ClientData $client): CreateClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Create($client),
        };
    }

    public function get(ClientData $client): GetClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Get($client),
        };
    }

    public function find(): FindClient
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Find,
        };
    }
}
