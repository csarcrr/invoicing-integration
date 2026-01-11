<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('has pdf has the default output', function (
    CreateInvoice $invoice,
    Fixtures $fixture
) {
    expect($invoice->getOutputFormat())->toBe(OutputFormat::PDF_BASE64);
})->with('create-invoice');

it('has the correct payload to request a pdf response', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->output()->files($fixtureName);

    $invoice->item(new Item(reference: 'item-1'));
    $invoice->outputFormat(OutputFormat::PDF_BASE64);
    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['has_pdf']);

it('has the correct payload to request a escpos response', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->output()->files($fixtureName);

    $invoice->item(new Item(reference: 'item-1'));
    $invoice->outputFormat(OutputFormat::ESCPOS);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['has_escpos']);

it('can save the output to pdf', function (CreateInvoice $invoice, Fixtures $fixture, IntegrationProvider $provider, string $fixtureName) {
    Http::fake(
        mockResponse(
            $provider,
            $fixture->response()->invoice()->output()->files($fixtureName)
        )
    );

    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->invoice();

    $path = "invoices/{$data->getOutput()->fileName()}";

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($path);
})->with('create-invoice', 'providers', ['output_with_pdf']);

it('can output escpos', function (CreateInvoice $invoice, Fixtures $fixture, IntegrationProvider $provider, string $fixtureName) {
    Http::fake(
        mockResponse(
            $provider,
            $fixture->response()->invoice()->output()->files($fixtureName)
        )
    );

    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->invoice();

    $path = "invoices/{$data->getOutput()->fileName()}";

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($path);
})->with('create-invoice', 'providers', ['output_with_escpos']);

it('can save the output under a custom name and path', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    IntegrationProvider $provider,
    string $fixtureName
) {
    Http::fake(
        mockResponse(
            $provider,
            $fixture->response()->invoice()->output()->files($fixtureName)
        )
    );

    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->invoice();

    $path = 'invoice/custom-name.pdf';

    $output = $data->getOutput()->save($path);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($path);
})->with('create-invoice', 'providers', ['output_with_pdf']);

it('is able to sanitize the path and filename when saving', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    IntegrationProvider $provider,
    string $fixtureName,
    string $invalidPath,
    string $expectedPath
) {
    Http::fake(
        mockResponse(
            $provider,
            $fixture->response()->invoice()->output()->files($fixtureName)
        )
    );

    $invoice->item(new Item(reference: 'item-1'));
    $data = $invoice->invoice();

    $output = $data->getOutput()->save($invalidPath);

    expect($output)->toBeString();
    Storage::disk('local')->assertExists($expectedPath);
})
    ->with('create-invoice', 'providers')
    ->with([
        ['output_with_pdf', '/absolute/path/file.pdf', 'absolute/path/file.pdf'],
        ['output_with_pdf', '\\windows\\path\\file.pdf', 'windows\\path\\file.pdf'],
        ['output_with_pdf', '//double/slash.pdf', 'double/slash.pdf'],
        ['output_with_pdf', '../../../etc/passwd', 'etc/passwd'],
        ['output_with_pdf', 'invoices/../../../secret', 'invoices/secret'],
        ['output_with_pdf', '..\\..\\windows\\system32', 'windows\\system32'],
        ['output_with_pdf', 'foo/..bar/baz', 'foo/bar/baz'],
        ['output_with_pdf', "file\x00name.pdf", 'file_name.pdf'],
        ['output_with_pdf', "file\x0Aname.pdf", 'file_name.pdf'],
        ['output_with_pdf', "file\x09name.pdf", 'file_name.pdf'],
        ['output_with_pdf', "file\x0Dname.pdf", 'file_name.pdf'],
        ['output_with_pdf', '/../../../\x00etc/passwd', 'etc/passwd'],
        ['output_with_pdf', 'FT 2026/001', 'ft_2026/001.pdf'],
        ['output_with_pdf', 'Invoice #123', 'invoice_123.pdf'],
        ['output_with_pdf', 'INVOICE 2026-001', 'invoice_2026_001.pdf'],
        ['output_with_pdf', '  Spaces Around  ', 'spaces_around.pdf'],
        ['output_with_pdf', 'Special@Chars!Here', 'specialcharshere.pdf'],
        ['output_with_pdf', 'Émojis 🎉 Test', 'emojis__test.pdf'],
        ['output_with_pdf', 'CamelCaseFileName', 'camelcasefilename.pdf'],
    ]);
