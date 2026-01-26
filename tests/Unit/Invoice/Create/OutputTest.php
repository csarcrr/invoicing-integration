<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\InvoiceWithoutOutputException;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('has pdf has the default output', function (Provider $provider) {
    $invoice = Invoice::create();

    expect($invoice->getOutputFormat())->toBe(OutputFormat::PDF_BASE64);
})->with('providers');

it('has the correct payload to request a pdf response', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $invoice->outputFormat(OutputFormat::PDF_BASE64);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_pdf']);

it('has the correct payload to request a escpos response', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->output()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $invoice->outputFormat(OutputFormat::ESCPOS);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['has_escpos']);

it('can save the output to pdf', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->execute();

    $path = "invoices/{$data->getOutput()->fileName()}";

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($path);
})->with('providers', ['output_with_pdf']);

it('can output escpos', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->execute();

    $path = "invoices/{$data->getOutput()->fileName()}";

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($path);
})->with('providers', ['output_with_escpos']);

it('can save the output under a custom name and path', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->execute();

    $path = 'invoices/custom-name.pdf';

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($output);
})->with('providers', ['output_with_pdf']);

it('is able to sanitize the path and filename when saving', function (
    Provider $provider,
    string $fixtureName,
    string $invalidPath,
    string $expectedPath
) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->execute();

    $savePath = $data->getOutput()->save($invalidPath);

    expect($savePath)->toBeString()->toBe($expectedPath);
    Storage::disk('local')->assertExists($savePath);
})
    ->with('providers', ['output_with_pdf'])
    ->with([
        ['/absolute/path/file.pdf', 'absolute/path/file.pdf'],
        ['\\windows\\path\\file.pdf', 'windows\\path\\file.pdf'],
        ['//double/slash.pdf', 'double/slash.pdf'],
        ['../../../etc/passwd', 'etc/passwd.pdf'],
        ['invoices/../../../secret', 'invoices/secret.pdf'],
        ['..\\..\\windows\\system32', 'windows\\system32.pdf'],
        ['foo/..bar/baz', 'foo/bar/baz.pdf'],
        ["file\x00name.pdf", 'filename.pdf'],
        ["file\x0Aname.pdf", 'file_name.pdf'],
        ["file\x09name.pdf", 'file_name.pdf'],
        ["file\x0Dname.pdf", 'file_name.pdf'],
        ['/../../../\x00etc/passwd', 'etc/passwd.pdf'],
        ['FT 2026/001', 'ft_2026/001.pdf'],
        ['Invoice #123', 'invoice_123.pdf'],
        ['INVOICE 2026-001', 'invoice_2026_001.pdf'],
        ['  Spaces Around  ', 'spaces_around.pdf'],
        ['Special@Chars!Here', 'specialcharshere.pdf'],
        ['Émojis 🎉 Test', 'emojis__test.pdf'],
        ['CamelCaseFileName', 'camelcasefilename.pdf'],
    ]);

it('outputs null when there is no invoice output provided', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->execute();

    $data->getOutput();
})->with('providers', ['output_with_no_output'])
    ->throws(InvoiceWithoutOutputException::class);
