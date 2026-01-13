<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client\Create as ClientCreate;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create as InvoiceCreate;

class CegidVendus
{
    protected array $config = [];

    public static function invoice(Action $action): mixed
    {
        $provider = new self;
        $provider->loadConfiguration();

        return match ($action) {
            Action::CREATE => new InvoiceCreate($provider->config)
        };
    }

    public static function client(Action $action): mixed
    {
        $provider = new self;
        $provider->loadConfiguration();

        return match ($action) {
            Action::CREATE => new ClientCreate($provider->config)
        };
    }

    protected function loadConfiguration(): void
    {
        $this->config = config('invoicing-integration.providers')[IntegrationProvider::CEGID_VENDUS->value];
    }
}
