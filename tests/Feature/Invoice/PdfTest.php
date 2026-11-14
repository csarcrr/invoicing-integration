<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('an invoice pdf request returns the pdf base64 encoded in the output', function (Provider $provider) {
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
})->with('providers');

test('the output url is the moloni pdf link and null on cegid vendus', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => ['*documents/123456.pdf' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf'])],
        Provider::MOLONI => [
            '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
            'www.moloni.pt/downloads/index.php*' => mockResponse('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']),
        ],
    });

    $output = Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute()->getOutput();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($output->url)->toBeNull(),
        Provider::MOLONI => expect($output->url)->toBe('https://www.moloni.pt/downloads/?h=abc.def.ghi'),
    };
})->with('providers')->note('todo: change test namings like these. we can have multiple new providers. this needs to be something more generic like "outputs the expected pdf content or pdf url"');

test('moloni keeps the pdf link when the download does not return a pdf', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake([
        '*documents/getPDFLink*' => mockResponse(fixtures()->response()->invoice()->files('pdf_link')),
        'www.moloni.pt/downloads/index.php*' => mockResponse('<html></html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $output = Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute()->getOutput();

    expect($output->content)->toBeNull()
        ->and($output->url)->toBe('https://www.moloni.pt/downloads/?h=abc.def.ghi');
})->note('todo: naming keeps being provider specific and it cannot. example fix: "providers that don\'t directly support download, are always able to fallback to get the url"');

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

test('fails when moloni returns an empty pdf link', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake([
        '*documents/getPDFLink*' => mockResponse(['url' => '']),
    ]);

    Invoice::pdf(InvoiceData::from(['id' => 123456]))->execute();
})->throws(RequestFailedException::class, 'Invoice PDF link not available.')->note('todo: this is a provider specific test');
