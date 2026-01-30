<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\InvoiceAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CreateInvoice create()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\InvoiceAction
 */
class Invoice extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return InvoiceAction::class;
    }
}
