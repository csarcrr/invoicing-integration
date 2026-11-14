<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\InvoiceAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateInvoice create(InvoiceData $invoice)
 * @method static ShouldGetInvoice get(InvoiceData $invoice)
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
