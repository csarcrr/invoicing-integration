<?php 

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('can apply a credit note reason', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->invoiceTypes()->files($fixtureName);

    $item = new Item(reference: 'reference-1');
    $item->relatedDocument('FT 01P2025/1', 1);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['credit_note']);

it('fails when reason is not applied', function (CreateInvoice $invoice, Fixtures $fixture) {
    $item = new Item(reference: 'reference-1');
    $item->relatedDocument('FT 01P2025/1', 1);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));

    $invoice->getPayload();
})->with('create-invoice')->throws(CreditNoteReasonIsMissingException::class);

it('results in nothing when applying reason to an invoice', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->invoiceTypes()->files($fixtureName);

    $item = new Item(reference: 'reference-1');
    $item->relatedDocument('FT 01P2025/1', 1);

    $invoice->item($item);
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['not_credit_note_reason_check']);