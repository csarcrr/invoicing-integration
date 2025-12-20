<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('can set the irs retention', function (bool $irsRetention, string $expectedValue) {
    $this->item->setReference('reference-1');

    $this->client->setVat('123456789');
    $this->client->setName('Name');
    $this->client->setIrsRetention($irsRetention);

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    $client = $resolve->payload()->get('client');
    expect($client['irs_retention'])->toBe($expectedValue);
})->with([
    [true, 'yes'],
    [false, 'no'],
]);

it('does not have irs retention when not set', function () {
    $this->item->setReference('reference-1');

    $this->client->setVat('123456789');
    $this->client->setName('Name');

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    $client = $resolve->payload()->get('client');
    expect(! isset($client['irs_retention']))->toBeTrue();
});
