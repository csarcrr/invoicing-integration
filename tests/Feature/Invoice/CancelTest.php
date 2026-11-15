<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;

test('a cancel request sends one request and throws nothing', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['id' => 123456, 'status' => 'A']),
        Provider::MOLONI => mockResponse(fixtures()->response()->invoice()->files('cancel')),
    });

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();

    Http::assertSentCount(1);
})->with('providers');

test('fails with the provider error when the document is not cancelled', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 400),
        Provider::MOLONI => mockResponse(['valid' => 0, 'message' => 'Document is not closed']),
    });

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();
})->with('providers')->throws(RequestFailedException::class);
