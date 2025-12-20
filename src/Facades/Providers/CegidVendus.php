<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades\Providers;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CsarCrr\InvoicingIntegration\InvoicingIntegration
 */
class CegidVendus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'CegidVendus';
    }
}
