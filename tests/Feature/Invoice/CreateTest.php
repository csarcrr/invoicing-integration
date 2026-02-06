<?php

declare(strict_types=1);

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

    $invoice = Invoice::create();
    $invoice->item(ItemData::make(['reference' => 'reference-1']));
    $invoice->payment(PaymentData::make(['method' => PaymentMethod::MB, 'amount' => 1000]));
    $invoice = $invoice->execute()->getInvoice();

    expect($invoice->id)->toBeInt()
        ->and($invoice->sequence)->toBeString()
        ->and($invoice->items)->toBeInstanceOf(Collection::class)
        ->and($invoice->items->first())->toBeInstanceOf(ItemData::class)
        ->and($invoice->payments)->toBeInstanceOf(Collection::class)
        ->and($invoice->payments->first())
        ->toBeInstanceOf(PaymentData::class);
})->with('providers', ['full']);

test('when creating request fails it handles errors properly', function (
    Provider $provider,
    string $fixtureName,
) {
    $payload = fixtures()->response()->invoice()->files($fixtureName);
    Http::fake(mockResponse($payload, 400));

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->execute();
})->with('providers', ['invoice_fail'])
    ->throws(RequestFailedException::class);

test('when auth in create fails it handles errors properly', function (
    Provider $provider,
    string $fixtureName,
) {
    $payload = fixtures()->response()->invoice()->files($fixtureName);

    Http::fake(mockResponse($payload, 401));

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->execute();
})->with('providers', ['invoice_auth'])
    ->throws(UnauthorizedException::class);

test('when provider fails catastrophically it handles the errors properly', function (
    Provider $provider,
) {
    Http::fake(mockResponse([], 500));

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->execute();
})->with('providers')
    ->throws(FailedReachingProviderException::class);
