<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

it('does not set items when issuing a RG', function () {
    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new Payment(PaymentMethod::MONEY, 500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(InvoiceType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');

    $resolve = Provider::resolve()->invoice()->create($invoicing);

    expect($resolve->payload()->get('items'))->toBeNull();
});

it('has a valid related documents payload', function () {
    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new Payment(amount: 500, method: PaymentMethod::MB);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(InvoiceType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');
    $invoicing->addRelatedDocument('FT 20000');

    $resolve = Provider::resolve()->invoice()->create($invoicing);

    expect(
        $resolve->payload()->get('invoices')
    )->toBeInstanceOf(Collection::class);

    expect(
        $resolve->payload()->get('invoices')->first()->get('document_number')
    )->toBe('FT 10000');

    expect(
        $resolve->payload()->get('invoices')->last()->get('document_number')
    )->toBe('FT 20000');
});

it('makes sure that invoices document numbers are string', function () {
    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new Payment(amount: 500, method: PaymentMethod::MB);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(InvoiceType::Receipt);
    $invoicing->addRelatedDocument('FT 1000');

    $resolve = Provider::resolve()->invoice()->create($invoicing);

    expect($resolve->payload()->get('invoices'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('invoices')->first())
        ->toBeInstanceOf(Collection::class);

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 1000');
});

it('makes sure it fails when no payments are set', function () {
    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setType(InvoiceType::Receipt);
    $invoicing->addRelatedDocument('FT 10000');

    $resolve = Provider::resolve()->invoice()->create($invoicing);
})->throws(MissingPaymentWhenIssuingReceiptException::class);
