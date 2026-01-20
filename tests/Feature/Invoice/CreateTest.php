<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Illuminate\Support\Facades\Http;

test('when creating request fails it handles errors properly', function (
    Provider $provider,
    string   $fixtureName,
) {
    $payload = fixtures()->response()->invoice()->files($fixtureName);
    Http::fake(mockResponse($payload, 400));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('providers', ['invoice_fail'])
    ->throws(RequestFailedException::class);

test('when auth in create fails it handles errors properly', function (
    Provider $provider,
    string   $fixtureName,
) {
    $payload = fixtures()->response()->invoice()->files($fixtureName);

    Http::fake(mockResponse($payload, 401));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('providers', ['invoice_auth'])
    ->throws(UnauthorizedException::class);

test('when provider fails catastrophically it handles the errors properly', function (
    Provider $provider,
) {
    Http::fake(mockResponse([], 500));

    $invoice = Invoice::create();
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('providers')
    ->throws(FailedReachingProviderException::class);
