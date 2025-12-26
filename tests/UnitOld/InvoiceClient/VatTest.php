<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client vat', function () {
    $this->client->setVat('123456789');
    expect($this->client->vat())->toBe('123456789');
});
