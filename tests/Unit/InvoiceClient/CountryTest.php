<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('sets and gets client country', function () {
    $this->client->setCountry('PT');
    expect($this->client->country())->toBe('PT');
});
