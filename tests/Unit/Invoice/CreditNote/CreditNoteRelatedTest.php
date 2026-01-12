<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('fails when no reason is provided on the item', function (
    CreateInvoice $invoice, Fixtures $fixture
) {
    $item = new Item(reference: 'reference-1');

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);

    $invoice->getPayload();
})->with('invoice-full')->throws(MissingRelatedDocumentException::class);
