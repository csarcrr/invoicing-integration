<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('assigns a client to an invoice', function () {
    $this->client->setVat('123456789');

    $this->invoice->setClient($this->client);

    expect($this->invoice->client()->vat)->toBe('123456789');
});
