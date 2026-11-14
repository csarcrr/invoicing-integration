<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Provider\Invoice as BaseInvoice;

use function collect;

abstract class Invoice extends BaseInvoice
{
    /**
     * @param  array<string, mixed>  $document
     */
    protected function toInvoiceData(array $document): InvoiceData
    {
        return InvoiceData::from([
            'id' => (int) $document['id'],
            'sequence' => (string) $document['number'],
            'total' => $this->toCents((float) $document['amount_gross']),
            'totalNet' => $this->toCents((float) $document['amount_net']),
            'atcudHash' => $document['atcud'] ?? null,
            'type' => InvoiceType::tryFrom($document['type'] ?? '') ?? InvoiceType::Invoice,
        ])->additional(collect($document)->except($this->supportedProperties)->toArray());
    }
}
