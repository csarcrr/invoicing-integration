<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;
use CsarCrr\InvoicingIntegration\ValueObjects\PaymentData;

it('assigns the correct due date payload', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->dueDate(Carbon::createFromFormat('Y-m-d', '2025-12-31'));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['due_date']);

it('fails setting a due date in a type different than FT', function (Provider $provider) {
    $invoice = Invoice::create();
    $invoice->type(InvoiceType::InvoiceReceipt);

    $invoice->payment(PaymentData::from([
        'method' => PaymentMethod::CREDIT_CARD,
        'amount' => 1000,
    ]));
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->dueDate(Carbon::now()->setDay(31)->setMonth(12)->setYear(2025));

    $invoice->getPayload();
})->with('providers')
    ->throws(Exception::class, 'Due date can only be set for FT document types.');
