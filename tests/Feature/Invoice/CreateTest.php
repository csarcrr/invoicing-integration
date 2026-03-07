<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

test('handles invoice response correctly', function (Provider $provider, string $fixture) {
    $payload = fixtures()->response()->invoice()->files($fixture);
    Http::fake(mockResponse($payload));

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::make(['reference' => 'reference-1'])],
        'payments' => [PaymentData::make(['method' => PaymentMethod::MB, 'amount' => 1000])],
    ]));

    $invoice = $invoice->execute()->getInvoice();

    expect($invoice->id)->toBeInt()
        ->and($invoice->sequence)->toBeString()
        ->and($invoice->items)->toBeInstanceOf(Collection::class)
        ->and($invoice->items->first())->toBeInstanceOf(ItemData::class)
        ->and($invoice->payments)->toBeInstanceOf(Collection::class)
        ->and($invoice->payments->first())
        ->toBeInstanceOf(PaymentData::class)
        ->and($invoice->getAdditionalData())->not->toBeEmpty();

})->with('providers', ['full']);
