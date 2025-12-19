<?php

use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
});

it('sets irs retention option', function (bool $value) {
    $this->client->setIrsRetention($value);

    expect($this->client->irsRetention())->toBe($value);
})->with([
    [true],
    [false],
]);
