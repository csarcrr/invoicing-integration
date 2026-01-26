<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Create;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Find;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client\Get;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

class ClientAction
{
    public function __construct(
        protected Provider $provider
    ) {}

    public function create(ValueObjects\ClientData $client): CreateClient
    {
        return match ($this->provider) {
            Provider::CEGID_VENDUS => new Create($client),
        };
    }

    public function get(ClientData $client): GetClient
    {
        return match ($this->provider) {
            Provider::CEGID_VENDUS => new Get($client),
        };
    }

    public function find(): FindClient
    {
        return match ($this->provider) {
            Provider::CEGID_VENDUS => new Find,
        };
    }
}
