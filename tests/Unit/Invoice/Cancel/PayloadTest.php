<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the cancel request for the invoice id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['id' => 123456, 'status' => 'A']),
        Provider::MOLONI => mockResponse(fixtures()->response()->invoice()->files('cancel')),
    });

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'PATCH'
            && Str::endsWith($request->url(), 'documents/123456')
            && $request->data()['status'] === 'A',
        Provider::MOLONI => Str::contains($request->url(), 'documents/documentCancel')
            && Str::contains($request->body(), 'document_id=123456'),
    });
})->with('providers');

it('sends the notes as observation on moloni only', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['id' => 123456, 'status' => 'A']),
        Provider::MOLONI => mockResponse(fixtures()->response()->invoice()->files('cancel')),
    });

    Invoice::cancel(InvoiceData::from(['id' => 123456, 'notes' => 'Wrong client']))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => ! Str::contains($request->body(), 'Wrong client'),
        Provider::MOLONI => Str::contains($request->body(), 'observation=Wrong+client'),
    });
})->with('providers')->note('todo: this cannot exist in the package. if the feature/field is only available in a specific provider, it should not be supported. this will steer users to believe it is supported in all providers and might result in unexected error or behaviours down the line. a package review must be done to remove all provider specific features and only support the common denominator of all providers. the test `omits the observation when no notes are set` also fits in this category');

it('omits the observation when no notes are set', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['id' => 123456, 'status' => 'A']),
        Provider::MOLONI => mockResponse(fixtures()->response()->invoice()->files('cancel')),
    });

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSent(fn (Request $request) => ! Str::contains($request->body(), 'observation')
        && ! Str::contains($request->body(), 'notes'));
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $invoice = InvoiceData::from([]);

    Invoice::cancel($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');
