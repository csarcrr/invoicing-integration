<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

it('downloads the pdf with the token from the pdf link and the document id', function () {
    Http::fake([
        '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
        'www.moloni.pt/downloads/index.php*' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']),
    ]);

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'www.moloni.pt/downloads/index.php')
        && Str::contains($request->url(), 'action=getDownload')
        && Str::contains($request->url(), 'h=abc.def.ghi')
        && Str::contains($request->url(), 'd=123456'));
});

it('fails when the pdf link is empty', function () {
    Http::fake([
        '*documents/getPDFLink*' => mockResponse(['url' => '']),
    ]);

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();
})->throws(RequestFailedException::class, 'Invoice PDF link not available.');
