<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('can set the invoice id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => ['*documents/123456.pdf' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf'])],
        Provider::MOLONI => [
            '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
            'www.moloni.pt/downloads/index.php*' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']),
        ],
    });

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::endsWith($request->url(), 'documents/123456.pdf'),
        Provider::MOLONI => Str::contains($request->body(), 'document_id=123456'),
    });
})->with('providers');

it('requests the moloni download with the token from the pdf link and the document id', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake([
        '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
        'www.moloni.pt/downloads/index.php*' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']),
    ]);

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'www.moloni.pt/downloads/index.php')
        && Str::contains($request->url(), 'action=getDownload')
        && Str::contains($request->url(), 'h=abc.def.ghi')
        && Str::contains($request->url(), 'd=123456'));
})->note('todo: this is a provider specific test');

it('fails when no id is set', function (Provider $provider) {
    $invoice = InvoiceData::from([]);

    Invoice::pdf($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');

it('fails when moloni returns an empty pdf link', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake([
        '*documents/getPDFLink*' => mockResponse(['url' => '']),
    ]);

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();
})->throws(RequestFailedException::class, 'Invoice PDF link not available.')->note('todo: this is a provider specific test');
