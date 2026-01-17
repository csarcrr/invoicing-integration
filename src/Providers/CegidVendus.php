<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client\Create as ClientCreate;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create as InvoiceCreate;
use Illuminate\Support\Facades\Http;

class CegidVendus
{
    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    public function __construct()
    {
        $this->loadConfiguration();
    }

    public static function invoice(Action $action): mixed
    {
        $provider = new self;

        return match ($action) {
            Action::CREATE => new InvoiceCreate($provider->getConfig())
        };
    }

    public static function client(Action $action): mixed
    {
        $provider = new self;

        return match ($action) {
            Action::CREATE => new ClientCreate($provider->getConfig())
        };
    }

    public static function setupHttpConfiguration(): mixed
    {
        $self = new self;
        $config = $self->getConfig();

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$config['key'],
        ])
            ->baseUrl('https://www.vendus.pt/ws/v1.1/')
            ->timeout(30)
            ->connectTimeout(10);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    protected function loadConfiguration(): void
    {
        $this->config = config('invoicing-integration.providers')[IntegrationProvider::CEGID_VENDUS->value];
    }
}
