<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('sends the cancel request for the invoice id', function (Provider $provider) {
    $cancel = Invoice::cancel(InvoiceData::from(['id' => 123456]));

    match ($provider) {
        Provider::VENDUS => expect($cancel->getUrl())->toBe('documents/123456')
            ->and($cancel->getPayload()->toArray())->toBe(['status' => 'A']),
        Provider::MOLONI => expect($cancel->getUrl())->toBe('documents/documentCancel')
            ->and($cancel->getPayload()->toArray())->toBe(['document_id' => 123456]),
    };
})->with('providers');

it('fails when no id is set', function () {
    $invoice = InvoiceData::from([]);

    Invoice::cancel($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');
