<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can apply a credit note reason', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->invoiceTypes()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from([
        'reference' => 'reference-1',
        'relatedDocument' => RelatedDocumentReferenceData::from([
            'documentId' => 'FT 01P2025/1',
            'row' => 1,
        ]),
    ]);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);
    $invoice->payment(PaymentData::from([
        'amount' => 1000,
        'method' => PaymentMethod::CREDIT_CARD,
    ]));
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['credit_note']);

it('fails when reason is not applied', function (Provider $provider) {
    $invoice = Invoice::create();
    $item = ItemData::from([
        'reference' => 'reference-1',
        'relatedDocument' => RelatedDocumentReferenceData::from([
            'documentId' => 'FT 01P2025/1',
            'row' => 1,
        ]),
    ]);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);
    $invoice->payment(PaymentData::from([
        'amount' => 1000,
        'method' => PaymentMethod::CREDIT_CARD,
    ]));

    $invoice->getPayload();
})->with('providers')->throws(CreditNoteReasonIsMissingException::class);

it('results in nothing when applying reason to an invoice', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->invoiceTypes()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from([
        'reference' => 'reference-1',
        'relatedDocument' => RelatedDocumentReferenceData::from([
            'documentId' => 'FT 01P2025/1',
            'row' => 1,
        ]),
    ]);

    $invoice->item($item);
    $invoice->payment(PaymentData::from([
        'amount' => 1000,
        'method' => PaymentMethod::CREDIT_CARD,
    ]));
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['not_credit_note_reason_check']);
