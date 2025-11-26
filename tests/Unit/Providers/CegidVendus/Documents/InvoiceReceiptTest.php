<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use CsarCrr\InvoicingIntegration\InvoicePayment;

it('fails when transport is set', function () {
    $payment = new InvoicePayment();
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('Countryland');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('Countryland');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->payments(collect([$payment]))
        ->transportDetails($transport)
        ->type(DocumentType::InvoiceReceipt);

    $resolve->create();
})->throws(InvoiceTypeDoesNotSupportTransportException::class);

it('fails when no items are set ', function () {
    $payment = new InvoicePayment();
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $resolve = app(config('invoicing-integration.provider'))
        ->payments(collect([$payment]))
        ->type(DocumentType::InvoiceReceipt);

    $resolve->create();
})->throws(InvoiceItemIsNotValidException::class);

it('fails when no payments are set', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::InvoiceReceipt);

    $resolve->create();
})->throws(MissingPaymentWhenIssuingReceiptException::class);