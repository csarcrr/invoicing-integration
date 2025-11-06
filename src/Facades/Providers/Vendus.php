<?php

namespace CsarCrr\InvoicingIntegration\Facades\Providers;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CsarCrr\InvoicingIntegration\InvoicingIntegration
 */
class Vendus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vendus';
    }
}
