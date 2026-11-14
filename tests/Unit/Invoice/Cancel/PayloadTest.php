<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('sends the cancel request for the invoice id', function (Provider $provider) {
    $cancel = Invoice::cancel(InvoiceData::from(['id' => 123456]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($cancel->getUrl())->toBe('documents/123456')
            ->and($cancel->getPayload()->toArray())->toBe(['status' => 'A']),
        Provider::MOLONI => expect($cancel->getUrl())->toBe('documents/documentCancel')
            ->and($cancel->getPayload()->toArray())->toBe(['document_id' => 123456]),
    };
})->with('providers');

it('sends the notes as observation on moloni only', function (Provider $provider) {
    $cancel = Invoice::cancel(InvoiceData::from(['id' => 123456, 'notes' => 'Wrong client']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($cancel->getPayload()->toArray())->toBe(['status' => 'A']),
        Provider::MOLONI => expect($cancel->getPayload()->toArray())->toBe(['document_id' => 123456, 'observation' => 'Wrong client']),
    };
})->with('providers')->note('todo: this cannot exist in the package. if the feature/field is only available in a specific provider, it should not be supported. this will steer users to believe it is supported in all providers and might result in unexected error or behaviours down the line. a package review must be done to remove all provider specific features and only support the common denominator of all providers. the test `omits the observation when no notes are set` also fits in this category');

it('omits the observation when no notes are set', function (Provider $provider) {
    $cancel = Invoice::cancel(InvoiceData::from(['id' => 123456]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($cancel->getPayload()->toArray())->toBe(['status' => 'A']),
        Provider::MOLONI => expect($cancel->getPayload()->toArray())->toBe(['document_id' => 123456]),
    };
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $invoice = InvoiceData::from([]);

    Invoice::cancel($invoice)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Invoice ID is required.');
