<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum IntegrationProvider: string
{
    case CEGID_VENDUS = 'CegidVendus';

    public function config(): mixed
    {
        return match ($this) {
            IntegrationProvider::CEGID_VENDUS => config('invoicing-integration.providers.CegidVendus')
        };
    }
}
