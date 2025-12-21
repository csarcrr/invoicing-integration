<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers\Builder\CegidVendus\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Providers\Builder\CegidVendus\Base;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice as ValueObjectsInvoice;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use Illuminate\Support\Collection;

abstract class InvoiceBase extends Base
{
    protected ?Client $client = null;

    protected ValueObjectsInvoice $invoice;

    protected ?TransportDetails $transportDetails = null;

    protected Collection $items;

    protected Collection $payments;

    protected Collection $relatedDocuments;

    protected Collection $payload;

    protected InvoiceType $type = InvoiceType::Invoice;

    protected ?Carbon $dueDate = null;

    protected array $invoiceTypesThatRequirePayments = [
        InvoiceType::Receipt,
        InvoiceType::InvoiceReceipt,
        InvoiceType::InvoiceSimple,
        InvoiceType::CreditNote,
    ];

    public function payload(): Collection
    {
        return $this->payload;
    }
}
