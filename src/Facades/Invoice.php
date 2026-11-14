<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\InvoiceAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldFindInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateInvoice create(InvoiceData $invoice)
 * @method static ShouldGetInvoice get(InvoiceData $invoice)
 * @method static ShouldGetInvoicePdf pdf(InvoiceData $invoice)
 * @method static ShouldCancelInvoice cancel(InvoiceData $invoice)
 * @method static ShouldFindInvoice find(?InvoiceData $invoice = null)
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
