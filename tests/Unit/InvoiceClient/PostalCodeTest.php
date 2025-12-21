<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client postal code', function () {
    $this->client->setPostalCode('0000-000');
    expect($this->client->postalCode())->toBe('0000-000');
});
