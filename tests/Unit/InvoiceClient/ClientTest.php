<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('sets and gets client address', function () {
    $this->client->setAddress('Rua das Flores 123');
    expect($this->client->address())->toBe('Rua das Flores 123');
});
