<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client\Create;

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
}
