<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('has the default type as FT', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->type()->files($fixtureName);
    $invoice->item(new Item(reference: 'reference-1'));

    expect($invoice->getPayload())->toMatchArray($data);

})->with('invoice-full', ['default_type']);

it('has the correct payload for invoices', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName, InvoiceType $type) {
    $data = $fixture->request()->invoice()->type()->files($fixtureName);

    $invoice->payment(new Payment(PaymentMethod::CREDIT_CARD, amount: 1000));
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->type($type);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('invoice-full')->with([
    ['default_type', InvoiceType::Invoice],
    ['fr_type', InvoiceType::InvoiceReceipt],
    ['fs_type', InvoiceType::InvoiceSimple],
]);
