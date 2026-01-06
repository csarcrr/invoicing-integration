<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;

class CegidVendus
{
    protected array $config = [];

    public static function invoice(Action $action): mixed
    {
        $provider = new self;
        $provider->loadConfiguration();

        return match ($action) {
            Action::CREATE => new Create($provider->config),
        };
    }

    protected function loadConfiguration(): void
    {
        $this->config = config('invoicing-integration.providers')[IntegrationProvider::CEGID_VENDUS->value];
    }
}
