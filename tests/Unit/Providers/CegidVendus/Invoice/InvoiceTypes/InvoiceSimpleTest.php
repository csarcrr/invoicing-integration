<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('fails when transport is set', function () {
    $payment = new Payment;
    $payment->setAmount(500);
    $payment->setMethod(PaymentMethod::CREDIT_CARD);

    $client = new Client;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $transport = new TransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setTransport($transport);
    $invoicing->setType(InvoiceType::InvoiceSimple);
    $invoicing->setClient($client);

    $resolve = Provider::resolve()->invoice()->create($invoicing);
})->throws(InvoiceTypeDoesNotSupportTransportException::class);

it('fails when no items are set ', function () {
    $payment = new Payment;
    $payment->setAmount(500);
    $payment->setMethod(PaymentMethod::CREDIT_CARD);

    $invoicing = Invoice::create();
    $invoicing->addPayment($payment);

    $resolve = Provider::resolve()->invoice()->create($invoicing);
})->throws(InvoiceItemIsNotValidException::class);

it('fails when no payments are set', function () {
    $item = new Item(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setType(InvoiceType::InvoiceSimple);

    $resolve = Provider::resolve()->invoice()->create($invoicing);
})->throws(MissingPaymentWhenIssuingReceiptException::class);
