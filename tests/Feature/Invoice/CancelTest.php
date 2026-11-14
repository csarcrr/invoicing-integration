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

test('moloni fails with the provider message when the document is not cancelled', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake([
        '*documents/documentCancel*' => mockResponse(['valid' => 0, 'message' => 'Document is not closed']),
    ]);

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();
})->throws(RequestFailedException::class, 'Document is not closed')->note('todo: this and "cegid vendus fails with the provider error when the request is rejected", are very similar in behaviour. the only thing that differs is the message. maybe unify the tests and do not look at the message type, but only to the exception type');

test('cegid vendus fails with the provider error when the request is rejected', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 400));

    Invoice::cancel(InvoiceData::from(['id' => 123456]))->execute();
})->throws(RequestFailedException::class, 'A001 - No data');
