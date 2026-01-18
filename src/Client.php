<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client\Create;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client\Get;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use function app;

final class Client
{
    public function __construct(
        protected IntegrationProvider $provider
    ) {}

    public static function create(ValueObjects\ClientData $client): CreateClient
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            IntegrationProvider::CEGID_VENDUS => new Create($client),
        };
    }

    public static function get(ClientData $client) : GetClient {
        $class = app()->make(self::class);

        return match ($class->provider) {
            IntegrationProvider::CEGID_VENDUS => new Get($client),
        };
    }
}
