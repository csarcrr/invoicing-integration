<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('sets and gets client phone', function () {
    $this->client->setPhone('123456789');
    expect($this->client->phone())->toBe('123456789');
});
