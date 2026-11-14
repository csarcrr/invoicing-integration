<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

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
