<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with due date', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
            ],
            'dueDate' => Carbon::createFromFormat('Y-m-d', '2025-12-31'),
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['due_date']);

it('fails setting a due date in a type different than FT', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::InvoiceSimple,
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'payments' => [
            PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
        ],
        'dueDate' => now()->addDay(),
    ]));

    $invoice->getPayload();
})->with('providers')
    ->throws(Exception::class, 'Due date can only be set for FT document types.');
