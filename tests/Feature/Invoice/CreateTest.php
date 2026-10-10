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
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('handles invoice response correctly', function (Provider $provider, string $fixture) {
    $payload = fixtures()->response()->invoice()->files($fixture);
    Http::fake(mockResponse($payload));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
        'payments' => [PaymentData::make(['method' => PaymentMethod::MB, 'amount' => 1000])],
    ]));

    $invoice = $invoice->execute()->getInvoice();

    expect($invoice->id)->toBeInt()
        ->and($invoice->sequence)->toBeString()
        ->and($invoice->items)->toBeInstanceOf(Collection::class)
        ->and($invoice->items->first())->toBeInstanceOf(ItemData::class)
        ->and($invoice->payments)->toBeInstanceOf(Collection::class)
        ->and($invoice->payments->first())
        ->toBeInstanceOf(PaymentData::class)
        ->and($invoice->getAdditionalData())->not->toBeEmpty();

})->with('providers', ['full']);

test('fills the sequence and totals from the provider response', function (Provider $provider, string $fixture) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files($fixture)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
    ]))->execute()->getInvoice();

    expect($invoice->total)->toBe(3000)
        ->and($invoice->totalNet)->toBe(2439);

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->sequence)->toBe('FT 01P2025/1'),
        Provider::MOLONI => expect($invoice->sequence)->toBe('FT A/12'),
    };
})->with('providers', ['full']);

test('fetches the created document when the provider needs it', function (Provider $provider, string $fixture) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files($fixture)));

    Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
    ]))->execute();

    match ($provider) {
        Provider::CEGID_VENDUS => Http::assertSentCount(1),
        Provider::MOLONI => Http::assertSent(
            fn (Request $request) => Str::contains($request->url(), '/v1/documents/getOne')
                && $request['document_id'] === 123456
        ),
    };
})->with('providers', ['full']);

test('sends each invoice type to the provider', function (Provider $provider, InvoiceType $type, string $moloniEndpoint) {
    fakeProviderPaymentMethods($provider);
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('full')));

    $attributes = ['reference' => 'reference-1'];
    $invoiceData = [
        'payments' => [PaymentData::from(['method' => PaymentMethod::CREDIT_CARD, 'amount' => 1000])],
        'type' => $type,
    ];

    if ($type === InvoiceType::CreditNote) {
        $attributes['relatedDocument'] = RelatedDocumentReferenceData::from([
            'documentId' => 'related-document-1',
            'row' => 1,
        ]);

        $invoiceData['creditNoteReason'] = 'Broken product';
    }

    $invoiceData['items'] = [ItemData::from($attributes)];

    Invoice::create(InvoiceData::make($invoiceData))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'documents') && $request['type'] === $type->value,
        Provider::MOLONI => Str::contains($request->url(), "/v1/{$moloniEndpoint}"),
    });
})->with('providers')->with([
    [InvoiceType::Invoice, 'invoices/insert'],
    [InvoiceType::InvoiceReceipt, 'invoiceReceipts/insert'],
    [InvoiceType::InvoiceSimple, 'simplifiedInvoices/insert'],
    [InvoiceType::Receipt, 'receipts/insert'],
    [InvoiceType::Transport, 'billsOfLading/insert'],
    [InvoiceType::CreditNote, 'creditNotes/insert'],
]);
