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
