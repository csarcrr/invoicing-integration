<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('sets and gets client city', function () {
    $this->client->setCity('Porto');
    expect($this->client->city())->toBe('Porto');
});
