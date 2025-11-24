<?php

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Facades\Http;

test('can invoice successfully with minimum data', function (array $integration, array $type) {
    Http::fake(buildFakeHttpResponses($integration, $type));

    $invoice = Invoice::create();
    $invoice->addItem(new InvoiceItem('reference-1'));

    $response = $invoice->invoice();

    expect($response)->toBeInstanceOf(InvoiceData::class);
    expect($response->sequence())->toBe('FT 10000');
})->with([
    [['vendus', 200], ['new_document']],
]);

test(
    'can invoice and emit a receipt for that invoice',
    function (array $integration, array $type) {
        Http::fake(buildFakeHttpResponses($integration, $type));

        $item = new InvoiceItem('reference-1');
        $item->setPrice(500);

        $invoice = Invoice::create();
        $invoice->setType(DocumentType::Invoice);
        $invoice->addItem($item);

        $details = $invoice->invoice();

        $receipt = Invoice::create();
        $receipt->setType(DocumentType::Receipt);
        $receipt->addPayment(new InvoicePayment(DocumentPaymentMethod::MONEY, 500));
        $receipt->addRelatedDocument($details->sequence());

        $details = $receipt->invoice();

        expect($details)->toBeInstanceOf(InvoiceData::class);
        expect($details->sequence())->toBe('RG 10000');
    }
)->with([
    [['vendus', 200], ['new_document', 'new_document' => ['number' => 'RG 10000']]],
]);

test('can handle integration errors', function () {})->todo();
