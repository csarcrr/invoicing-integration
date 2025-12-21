<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
});

it('sets irs retention option', function (bool $value) {
    $this->client->setIrsRetention($value);

    expect($this->client->irsRetention())->toBe($value);
})->with([
    [true],
    [false],
]);
