<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('fails when no reason is provided on the item', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from(['reference' => 'reference-1'])]
    ]));

    $invoice->getPayload();
})->with('providers')->throws(MissingRelatedDocumentException::class);
