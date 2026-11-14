<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the invoice type FT by default', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Invoice::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'type=FT'),
        Provider::MOLONI => Str::contains($request->url(), 'invoices/getAll'),
    });
})->with('providers');

it('sends the document type of the filter', function (Provider $provider, InvoiceType $type) {
    Http::fake(mockResponse([]));

    Invoice::find(InvoiceData::from(['type' => $type]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'type='.$type->value),
        Provider::MOLONI => Str::contains($request->url(), match ($type) {
            InvoiceType::Invoice => 'invoices/getAll',
            InvoiceType::InvoiceReceipt => 'invoiceReceipts/getAll',
            InvoiceType::InvoiceSimple => 'simplifiedInvoices/getAll',
            InvoiceType::Receipt => 'receipts/getAll',
            InvoiceType::Transport => 'billsOfLading/getAll',
            InvoiceType::CreditNote => 'creditNotes/getAll',
        }),
    });
})->with('providers', InvoiceType::cases());

it('sends the client id of the filter', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Invoice::find(InvoiceData::from(['client' => ['id' => '12001']]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'client_id=12001'),
        Provider::MOLONI => Str::contains($request->body(), 'customer_id=12001'),
    });
})->with('providers');

it('does not send a client filter when no client is set', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Invoice::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => ! Str::contains($request->url(), 'client_id'),
        Provider::MOLONI => ! Str::contains($request->body(), 'customer_id'),
    });
})->with('providers');

it('sends the requested page', function (Provider $provider) {
    Http::fake(mockResponse([]));

    Invoice::find()->page(2)->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'page=2'),
        Provider::MOLONI => Str::contains($request->body(), 'offset=20'),
    });
})->with('providers');
