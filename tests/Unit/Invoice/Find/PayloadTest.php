<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('does not send a document type when no type is set', function (Provider $provider) {
    $find = Invoice::find();

    match ($provider) {
        Provider::VENDUS => expect($find->getPayload()->toArray())->not->toHaveKey('type'),
        Provider::MOLONI => expect($find->getUrl())->toBe('documents/getAll'),
    };
})->with('providers');

it('sends the document type of the filter', function (Provider $provider, InvoiceType $type) {
    $find = Invoice::find(InvoiceData::from(['type' => $type]));

    match ($provider) {
        Provider::VENDUS => expect($find->getPayload()->get('type'))->toBe($type->value),
        Provider::MOLONI => expect($find->getUrl())->toBe(match ($type) {
            InvoiceType::Invoice => 'invoices/getAll',
            InvoiceType::InvoiceReceipt => 'invoiceReceipts/getAll',
            InvoiceType::InvoiceSimple => 'simplifiedInvoices/getAll',
            InvoiceType::Receipt => 'receipts/getAll',
            InvoiceType::Transport => 'billsOfLading/getAll',
            InvoiceType::CreditNote => 'creditNotes/getAll',
        }),
    };
})->with('providers', InvoiceType::cases());

it('sends the client id of the filter', function (Provider $provider) {
    $find = Invoice::find(InvoiceData::from(['client' => ['id' => '12001']]));

    match ($provider) {
        Provider::VENDUS => expect($find->getPayload()->get('client_id'))->toBe('12001'),
        Provider::MOLONI => expect($find->getPayload()->get('customer_id'))->toBe(12001),
    };
})->with('providers');

it('does not send a client filter when no client is set', function (Provider $provider) {
    $find = Invoice::find();

    match ($provider) {
        Provider::VENDUS => expect($find->getPayload()->toArray())->not->toHaveKey('client_id'),
        Provider::MOLONI => expect($find->getPayload()->toArray())->not->toHaveKey('customer_id'),
    };
})->with('providers');

it('sends the requested page', function (Provider $provider) {
    $find = Invoice::find()->page(2);

    match ($provider) {
        Provider::VENDUS => expect($find->getPayload()->get('page'))->toBe(2),
        Provider::MOLONI => expect($find->getPayload()->get('offset'))->toBe(20),
    };
})->with('providers');
