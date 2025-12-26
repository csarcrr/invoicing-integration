<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client city', function () {
    $this->client->setCity('Porto');
    expect($this->client->city())->toBe('Porto');
});
