<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('fails to invoice when client has name but no vat', function () {
    $this->client->setName('John Doe');
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $this->invoice->invoice();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $this->client->setVat('');
    $this->invoice = Invoice::create();
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $this->invoice->invoice();
})->throws(InvoiceRequiresClientVatException::class);
