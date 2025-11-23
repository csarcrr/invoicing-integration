<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;

it('can set transport type', function () {
    $invoice = Invoice::create();
    $invoice->setType(DocumentType::Transport);

    expect($invoice->type())->toBe(DocumentType::Transport);
});

it('can set origin details', function () {
    $invoice = Invoice::create();
    $invoice->setType(DocumentType::Transport);

    $transport = new InvoiceTransportDetails();
    $transport->origin()->date(now()->addWeek());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St, Springfield');
    $transport->origin()->city('Springfield');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('USA');

    $invoice->setTransport($transport);

    expect($invoice->transport())->toBeInstanceOf(InvoiceTransportDetails::class);
    expect($invoice->transport()->origin()->address())->toBe('123 Main St, Springfield');
    expect($invoice->transport()->origin()->date())->toBeInstanceOf(\Carbon\Carbon::class);
});

it('can set destination details', function () {
    $invoice = Invoice::create();
    $invoice->setType(DocumentType::Transport);

    $transport = new InvoiceTransportDetails();
    $transport->destination()->date(now()->addWeek());
    $transport->destination()->time(now());
    $transport->destination()->address('123 Main St, Springfield');
    $transport->destination()->city('Springfield');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('USA');

    $invoice->setTransport($transport);

    expect($invoice->transport())->toBeInstanceOf(InvoiceTransportDetails::class);
    expect($invoice->transport()->destination()->address())->toBe('123 Main St, Springfield');
    expect($invoice->transport()->destination()->date())->toBeInstanceOf(\Carbon\Carbon::class);
});

it('can set a vehicle license plate', function () {
    $invoice = Invoice::create();
    $invoice->setType(DocumentType::Transport);

    $transport = new InvoiceTransportDetails();
    $transport->vehicleLicensePlate('ABC-1234');

    $invoice->setTransport($transport);

    expect($invoice->transport())->toBeInstanceOf(InvoiceTransportDetails::class);
    expect($invoice->transport()->vehicleLicensePlate())->toBe('ABC-1234');
});
