<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
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

test('returns the atcud of the created invoice', function (Provider $provider, string $fixture) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files($fixture)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
    ]))->execute()->getInvoice();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->atcudHash)->toBe('INVALIDO-1'),
        Provider::MOLONI => expect($invoice->atcudHash)->toBeNull(),
    };
})->with('providers', ['full']);

test('does not keep the provider document lines as additional data', function (Provider $provider, string $fixture) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files($fixture)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
    ]))->execute()->getInvoice();

    expect($invoice->getAdditionalData())->not->toHaveKeys(['products', 'items']);
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

test('records the credit note payments as a refund', function (Provider $provider) {
    fakeProviderPaymentMethods($provider);
    Http::fake(['*paymentReturns/insert*' => mockResponse(['valid' => 1, 'document_id' => 654321])]);
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('full')));

    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from([
            'reference' => 'reference-1',
            'price' => 1000,
            'relatedDocument' => RelatedDocumentReferenceData::from(['documentId' => '111111', 'row' => 1]),
        ])],
        'payments' => [PaymentData::from(['method' => PaymentMethod::MONEY, 'amount' => 1000])],
        'creditNoteReason' => 'Broken product',
    ]))->execute()->getInvoice();

    match ($provider) {
        Provider::CEGID_VENDUS => Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'documents')
            && isset($request['payments'])),
        Provider::MOLONI => Http::assertSent(fn (Request $request) => Str::contains($request->url(), '/v1/creditNotes/insert')
            && ! isset($request['payments'])),
    };

    match ($provider) {
        Provider::CEGID_VENDUS => Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'paymentReturns')),
        Provider::MOLONI => Http::assertSent(fn (Request $request) => Str::contains($request->url(), '/v1/paymentReturns/insert')
            && $request['customer_id'] === 999999
            && $request['document_set_id'] === 8002
            && $request['net_value'] === 10.0
            && $request['associated_documents'] === [['associated_id' => 123456, 'value' => 10.0]]
            && $request['payments'] === [['payment_method_id' => 7001, 'date' => Carbon::now()->toDateString(), 'value' => 10]]
            && $request['status'] === 1),
    };

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getAdditionalData())->not->toHaveKey('payment_return_id'),
        Provider::MOLONI => expect($invoice->getAdditionalData()['payment_return_id'])->toBe(654321),
    };
})->with('providers');

test('does not record a refund for a credit note without payments', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('full')));

    Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from([
            'reference' => 'reference-1',
            'relatedDocument' => RelatedDocumentReferenceData::from(['documentId' => '111111', 'row' => 1]),
        ])],
        'creditNoteReason' => 'Broken product',
    ]))->execute();

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'paymentReturns'));
})->with('providers');

test('does not record a refund for a draft credit note', function (Provider $provider) {
    fakeProviderPaymentMethods($provider);
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('full')));

    Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from([
            'reference' => 'reference-1',
            'relatedDocument' => RelatedDocumentReferenceData::from(['documentId' => '111111', 'row' => 1]),
        ])],
        'payments' => [PaymentData::from(['method' => PaymentMethod::MONEY, 'amount' => 1000])],
        'creditNoteReason' => 'Broken product',
    ]))->asDraft()->execute();

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'paymentReturns'));

    match ($provider) {
        Provider::CEGID_VENDUS => Http::assertSent(fn (Request $request) => isset($request['payments'])),
        Provider::MOLONI => Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'paymentMethods/getAll')),
    };
})->with('providers');

test('fails when the provider rejects the credit note refund', function (Provider $provider) {
    fakeProviderPaymentMethods($provider);
    Http::fake(['*paymentReturns/insert*' => mockResponse([['code' => '2 net_value', 'description' => 'Invalid value']])]);
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('full')));

    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::CreditNote,
        'items' => [ItemData::from([
            'reference' => 'reference-1',
            'relatedDocument' => RelatedDocumentReferenceData::from(['documentId' => '111111', 'row' => 1]),
        ])],
        'payments' => [PaymentData::from(['method' => PaymentMethod::MONEY, 'amount' => 1000])],
        'creditNoteReason' => 'Broken product',
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect(fn () => $invoice->execute())->not->toThrow(RequestFailedException::class),
        Provider::MOLONI => expect(fn () => $invoice->execute())->toThrow(RequestFailedException::class, '2 net_value - Invalid value'),
    };
})->with('providers');
