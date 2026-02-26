<?php

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('can save the output to pdf', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'item-1'])],
    ]));

    $data = $invoice->execute()->getInvoice();

    $path = "invoices/{$data->output->fileName}";

    $output = $data->output->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($output);
})->with('providers', ['output_with_pdf']);

test('can output escpos', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'item-1'])],
    ]));

    $data = $invoice->execute()->getInvoice();

    $path = "invoices/{$data->output->fileName}";

    $output = $data->output->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($output);
})->with('providers', ['output_with_escpos']);

test('can save the output under a custom name and path', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'item-1'])],
    ]));

    $data = $invoice->execute()->getInvoice();

    $path = 'invoices/custom-name.pdf';

    $output = $data->output->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($output);
})->with('providers', ['output_with_pdf']);

test('is able to sanitize the path and filename when saving', function (
    Provider $provider,
    string $fixtureName,
    string $invalidPath,
    string $expectedPath
) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'item-1'])],
    ]));

    $data = $invoice->execute()->getInvoice();

    $savePath = $data->output->save($invalidPath);

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

test('outputs null when there is no invoice output provided', function (Provider $provider, string $fixtureName) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->output()->files($fixtureName)));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'item-1'])],
    ]));

    $data = $invoice->execute()->getInvoice();

    expect($data->output)->toBeInstanceOf(OutputData::class)->and($data->output->content)->toBeNull();
})->with('providers', ['output_with_no_output']);
