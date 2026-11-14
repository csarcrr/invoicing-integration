<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;

it('can set the invoice id', function (Provider $provider) {
    $get = Invoice::get(InvoiceData::from(['id' => 123456]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($get->getUrl())->toBe('documents/123456')
            ->and($get->getPayload()->toArray())->toBe([]),
        Provider::MOLONI => expect($get->getUrl())->toBe('documents/getOne')
            ->and($get->getPayload()->toArray())->toBe(['document_id' => 123456]),
    };
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $invoice = InvoiceData::from([]);

    Invoice::get($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');

it('fails when the provider has no invoice with the id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404),
        Provider::MOLONI => mockResponse([]),
    });

    Invoice::get(InvoiceData::from(['id' => 123456]))->execute();
})->with('providers')->throws(RequestFailedException::class);
