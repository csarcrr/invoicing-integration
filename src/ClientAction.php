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
use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;

use function app;

class ClientAction
{
    public function __construct(
        protected Provider $provider
    ) {}

    public static function create(ValueObjects\ClientDataObject $client): CreateClient
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            Provider::CEGID_VENDUS => new Create($client),
        };
    }

    public static function get(ClientDataObject $client): GetClient
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            Provider::CEGID_VENDUS => new Get($client),
        };
    }

    public static function find(): FindClient
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            Provider::CEGID_VENDUS => new Find,
        };
    }
}
