<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('fails when no reason is provided on the item', function (Provider $provider) {
    $invoice = Invoice::create();
    $item = new Item(reference: 'reference-1');

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);

    $invoice->getPayload();
})->with('providers')->throws(MissingRelatedDocumentException::class);
