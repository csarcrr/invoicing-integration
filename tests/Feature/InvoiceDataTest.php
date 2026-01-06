<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Facades\Http;

it('it has the valid invoice data after issuing', function (CreateInvoice $create, Fixtures $fixture, IntegrationProvider $provider, string $fixtureName) {
    $data = $fixture->response()->invoice()->files($fixtureName);

    Http::fake(mockResponse($provider, $data));

    $create->item(new Item(reference: 'reference-1'));
    $create->payment(new Payment(method: PaymentMethod::CREDIT_CARD, amount: 1000));

    $invoice = $create->invoice();

    expect($invoice->getAtcudHash())->not->toBeEmpty();
    expect($invoice->getId())->not->toBeEmpty();
    expect($invoice->getSequence())->not->toBeEmpty();
    expect($invoice->getTotal())->not->toBeEmpty();
    expect($invoice->getOutput())->not->toBeEmpty();
})->with('create-invoice', 'providers', ['full']);
