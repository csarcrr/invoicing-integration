<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

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
        $total = (float) ($document['net_value'] ?? 0);

        return InvoiceData::from([
            'id' => (int) $document['document_id'],
            'sequence' => $this->sequence($document),
            'total' => $this->toCents($total),
            'totalNet' => $this->toCents($total - (float) ($document['taxes_value'] ?? 0)),
            'type' => InvoiceType::tryFrom($document['document_type']['saft_code'] ?? '') ?? InvoiceType::Invoice,
        ])->additional(collect($document)->except($this->supportedProperties)->toArray());
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function sequence(array $document): string
    {
        $type = $document['document_type']['saft_code'] ?? '';
        $set = $document['document_set']['name'] ?? '';
        $number = $document['number'] ?? 0;

        return "{$type} {$set}/{$number}";
    }
}
