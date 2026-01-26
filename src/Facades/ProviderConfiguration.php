<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Services\ProviderConfigurationService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Provider getProvider()
 * @method static array<string, mixed> getConfig()
 * @method static mixed get(string $key, mixed $default = null)
 *
 * @see \CsarCrr\InvoicingIntegration\Services\ProviderConfigurationService
 */
class ProviderConfiguration extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ProviderConfigurationService::class;
    }
}
