<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with related document', function (Provider $provider, string $fixtureName, InvoiceType $type) {
    $data = fixtures()->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'type' => $type,
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'payments' => [PaymentData::from(['amount' => 1000, 'method' => PaymentMethod::CREDIT_CARD])],
        'relatedDocument' => '99999999',
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['normal_related_document', InvoiceType::Invoice],
    ['normal_related_document', InvoiceType::InvoiceReceipt],
    ['normal_related_document', InvoiceType::InvoiceSimple],
    ['normal_related_document', InvoiceType::Receipt],
]);

it('transforms to provider payload with credit note related document', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from(['reference' => 'reference-1', 'relatedDocument' => RelatedDocumentReferenceData::from(['documentId' => 'FT 01P2025/1', 'row' => 1])])],
        'payments' => [PaymentData::from(['amount' => 1000, 'method' => PaymentMethod::CREDIT_CARD])],
        'relatedDocument' => 'FT 01P2025/1',
        'creditNoteReason' => 'Product damaged',
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['nc_related_document']);
