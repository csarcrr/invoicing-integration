<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;

test('returns the invoice pdf content and the provider pdf url', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => ['*documents/123456.pdf' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf'])],
        Provider::MOLONI => [
            '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
            'www.moloni.pt/downloads/index.php*' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']),
        ],
    });

    $output = Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute()->getOutput();

    expect(base64_decode($output->content))->toBe('%PDF-1.7 test')
        ->and($output->format)->toBe(OutputFormat::PDF_BASE64);

    match ($provider) {
        Provider::CEGID_VENDUS => expect($output->url)->toBeNull(),
        Provider::MOLONI => expect($output->url)->toBe('https://www.moloni.pt/downloads/?h=abc.def.ghi'),
    };
})->with('providers');

test('falls back to the pdf url when the provider download does not return a pdf', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus returns the pdf directly and has no pdf url.');
    }

    Http::fake([
        '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
        'www.moloni.pt/downloads/index.php*' => mockResponse('<html></html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $output = Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute()->getOutput();

    expect($output->content)->toBeNull()
        ->and($output->url)->toBe('https://www.moloni.pt/downloads/?h=abc.def.ghi');
})->with('providers');
