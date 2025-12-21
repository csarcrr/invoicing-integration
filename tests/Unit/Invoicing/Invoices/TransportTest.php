<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set transport type', function () {
    $this->invoice->setType(InvoiceType::Transport);

    expect($this->invoice->type())->toBe(InvoiceType::Transport);
});

it('can set origin details', function () {
    $this->invoice->setType(InvoiceType::Transport);

    $transport = new TransportDetails;
    $transport->origin()->date(now()->addWeek());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St, Springfield');
    $transport->origin()->city('Springfield');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $this->invoice->setTransport($transport);

    expect($this->invoice->transport())->toBeInstanceOf(TransportDetails::class);
    expect($this->invoice->transport()->origin()->address())->toBe('123 Main St, Springfield');
    expect($this->invoice->transport()->origin()->date())->toBeInstanceOf(\Carbon\Carbon::class);
});

it('can set destination details', function () {
    $this->invoice->setType(InvoiceType::Transport);

    $transport = new TransportDetails;
    $transport->destination()->date(now()->addWeek());
    $transport->destination()->time(now());
    $transport->destination()->address('123 Main St, Springfield');
    $transport->destination()->city('Springfield');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $this->invoice->setTransport($transport);

    expect($this->invoice->transport())->toBeInstanceOf(TransportDetails::class);
    expect($this->invoice->transport()->destination()->address())->toBe('123 Main St, Springfield');
    expect($this->invoice->transport()->destination()->date())->toBeInstanceOf(\Carbon\Carbon::class);
});

it('can set a vehicle license plate', function () {
    $this->invoice->setType(InvoiceType::Transport);

    $transport = new TransportDetails;
    $transport->vehicleLicensePlate('ABC-1234');

    $this->invoice->setTransport($transport);

    expect($this->invoice->transport())->toBeInstanceOf(TransportDetails::class);
    expect($this->invoice->transport()->vehicleLicensePlate())->toBe('ABC-1234');
});
