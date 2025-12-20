<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('sets and gets client name', function () {
    $this->client->setName('Joao Alberto');
    expect($this->client->name())->toBe('Joao Alberto');
});
