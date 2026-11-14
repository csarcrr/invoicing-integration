<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Provider\Invoice as BaseInvoice;

abstract class Invoice extends BaseInvoice
{
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
