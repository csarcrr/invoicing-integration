<?php


use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
});

it('sets irs retention option to true', function () {
    $this->client->setIrsRetention(true);

    expect($this->client->irsRetention())->toBeTrue();
});

it('sets irs retention option to false', function () {
    $this->client->setIrsRetention(false);

    expect($this->client->irsRetention())->toBeFalse();
});
