<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client country', function () {
    $this->client->setCountry('PT');
    expect($this->client->country())->toBe('PT');
});
