<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('applies a note to the invoice', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->files($fixtureName);

    $invoice->notes('This is a note for the invoice.');
    $invoice->item(new Item(reference: 'reference-1'));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['notes']);
