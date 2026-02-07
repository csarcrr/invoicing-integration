<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with default invoice type', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->type()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['default_type']);

it('transforms to provider payload with correct invoice type', function (Provider $provider, string $fixtureName, InvoiceType $type) {
    $data = fixtures()->request()->invoice()->type()->files($fixtureName);

    $invoice = Invoice::create();
    $attributes = ['reference' => 'reference-1'];

    if ($type === InvoiceType::CreditNote) {
        $attributes['relatedDocument'] = RelatedDocumentReferenceData::from([
            'documentId' => 'related-document-1',
            'row' => 1,
        ]);
        $invoice->creditNoteReason('Reason for credit note');
    }

    $invoice->payment(PaymentData::from([
        'method' => PaymentMethod::CREDIT_CARD,
        'amount' => 1000,
    ]));
    $invoice->item(ItemData::from($attributes));
    $invoice->type($type);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['default_type', InvoiceType::Invoice],
    ['fr_type', InvoiceType::InvoiceReceipt],
    ['fs_type', InvoiceType::InvoiceSimple],
    ['rg_type', InvoiceType::Receipt],
    ['gt_type', InvoiceType::Transport],
    ['nc_type', InvoiceType::CreditNote],
]);
