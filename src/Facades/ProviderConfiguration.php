<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfiguration;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Provider getProvider()
 * @method static array<string, mixed> getConfig()
 * @method static mixed get(string $key, mixed $default = null)
 *
 * @see \CsarCrr\InvoicingIntegration\Configuration\ProviderConfiguration
 */
class ProviderConfiguration extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ProviderConfiguration::class;
    }
}
