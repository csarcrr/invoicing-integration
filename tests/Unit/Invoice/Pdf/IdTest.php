<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can set the invoice id', function (Provider $provider) {
    $pdf = Invoice::pdf(InvoiceData::from(['id' => 123456]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($pdf->getUrl())->toBe('documents/123456.pdf')
            ->and($pdf->getPayload()->toArray())->toBe([]),
        Provider::MOLONI => expect($pdf->getUrl())->toBe('documents/getPDFLink')
            ->and($pdf->getPayload()->toArray())->toBe(['document_id' => 123456]),
    };
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $invoice = InvoiceData::from([]);

    Invoice::pdf($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');
