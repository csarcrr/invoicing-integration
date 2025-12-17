<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem();
    $this->client = new InvoiceClient();
});

it('fails when transport is set', function () {
    $payment = new InvoicePayment;
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $this->client->setVat('1234567890');
    $this->client->setName('Client Name');
    $this->client->setCountry('PT');
    $this->client->setAddress('Client Address');
    $this->client->setCity('Client City');
    $this->client->setPostalCode('4410-100');

    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $transport = new InvoiceTransportDetails;
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

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);
    $this->invoice->setTransport($transport);
    $this->invoice->setType(DocumentType::InvoiceReceipt);
    $this->invoice->setClient($this->client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(InvoiceTypeDoesNotSupportTransportException::class);

it('fails when no items are set ', function () {
    $payment = new InvoicePayment;
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $this->invoice->addPayment($payment);
    $this->invoice->setType(DocumentType::InvoiceReceipt);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(InvoiceItemIsNotValidException::class);

it('fails when no payments are set', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setType(DocumentType::InvoiceReceipt);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(MissingPaymentWhenIssuingReceiptException::class);
