<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum IntegrationProvider: string
{
    case CEGID_VENDUS = 'CegidVendus';

    public static function current(): self
    {
        return self::from(config('invoicing-integration.provider'));
    }

    public function config(): mixed
    {
        return match ($this) {
            IntegrationProvider::CEGID_VENDUS => config('invoicing-integration.providers.CegidVendus')
        };
    }
}
