<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('fails to invoice when client has name but no vat', function () {
    $this->client->setName('John Doe');
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new Item('reference-1'));

    $this->invoice->data();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $this->client->setVat('');
    $this->invoice = Invoice::create();
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new Item('reference-1'));

    $this->invoice->data();
})->throws(InvoiceRequiresClientVatException::class);
