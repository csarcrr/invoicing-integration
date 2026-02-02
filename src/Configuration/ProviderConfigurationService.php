<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Configuration;

use CsarCrr\InvoicingIntegration\Enums\Provider;

class ProviderConfigurationService
{
    protected Provider $provider;

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct()
    {
        $this->provider = Provider::from(config('invoicing-integration.provider'));
        $this->config = config('invoicing-integration.providers')[$this->provider->value];
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param  string  $key  configuration key in dot notation
     * @param  mixed|null  $default  value returned when the key does not exist
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
