<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider;

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Traits\ProviderConfiguration;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPackageTools\Concerns\Package\HasConfigs;

final class Request
{
    use ProviderConfiguration;

    public function __construct(
        protected IntegrationProvider $provider,
        protected Collection $config
    ) {
        $this->config($config);
    }

    static public function get(
        IntegrationProvider $provider,
        Collection $config
    ): PendingRequest {
        $self = new self($provider, $config);

        return match ($provider) {
            IntegrationProvider::CEGID_VENDUS => $self->cegidVendus(),
        };
    }

    public function getProvider(): IntegrationProvider
    {
        return $this->provider;
    }

    protected function cegidVendus(): PendingRequest
    {

        return Http::withHeader(
            'Authorization',
            'Bearer ' . $this->getConfig()->get('key')
        )->baseUrl('https://www.vendus.pt/ws/documents/');
    }
}
