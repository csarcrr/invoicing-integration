<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

final class Request implements ShouldHaveConfig
{
    use HasConfig;

    /**
     * @param  Collection<string, mixed>  $config
     */
    public function __construct(
        protected IntegrationProvider $provider,
        protected Collection $config
    ) {
        $this->config($config);
    }

    /**
     * @param  Collection<string, mixed>  $config
     */
    public static function get(
        IntegrationProvider $provider,
        Collection $config
    ): PendingRequest {
        $self = new self($provider, $config);

        $request = match ($provider) {
            IntegrationProvider::CEGID_VENDUS => $self->cegidVendus(),
        };

        $request->asJson();
        $request->acceptJson();

        return $request;
    }

    public function getProvider(): IntegrationProvider
    {
        return $this->provider;
    }

    protected function cegidVendus(): PendingRequest
    {
        return Http::withHeader(
            'Authorization',
            'Bearer '.$this->getConfig()->get('key')
        )
            ->baseUrl('https://www.vendus.pt/ws/v1.1/')
            ->timeout(30)
            ->connectTimeout(10);
    }
}
