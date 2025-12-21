<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client address', function () {
    $this->client->setAddress('Rua das Flores 123');
    expect($this->client->address())->toBe('Rua das Flores 123');
});
