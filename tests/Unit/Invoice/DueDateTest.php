<?php 

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('assigns the correct due date payload', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->files($fixtureName);

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->dueDate(Carbon::now()->setDay(31)->setMonth(12)->setYear(2025));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['due_date']);

it('fails setting a due date in a type different than FT', function (CreateInvoice $invoice, Fixtures $fixture) {
    $invoice->type(InvoiceType::InvoiceReceipt);

    $invoice->payment(new Payment(method: PaymentMethod::CREDIT_CARD, amount: 1000));
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->dueDate(Carbon::now()->setDay(31)->setMonth(12)->setYear(2025));

    $invoice->getPayload();
})->with('create-invoice')
    ->throws(Exception::class, 'Due date can only be set for FT document types.');