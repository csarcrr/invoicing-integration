<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CsarCrr\InvoicingIntegration\InvoicingIntegration
 */
class Invoice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'invoice';
    }
}
