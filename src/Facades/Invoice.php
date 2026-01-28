<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\InvoiceAction;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CreateInvoice create()
 *
 * @see \CsarCrr\InvoicingIntegration\InvoiceAction
 */
class Invoice extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return InvoiceAction::class;
    }
}
