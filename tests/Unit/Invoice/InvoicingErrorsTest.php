<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Illuminate\Support\Facades\Http;

it('handles invoicing input errors properly', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    IntegrationProvider $provider,
    string $fixtureName,
) {
    $payload = $fixture->response()->invoice()->files($fixtureName);
    Http::fake(mockResponse($provider, $payload, 400));

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('invoice-full', 'providers', ['invoice_fail'])
    ->throws(RequestFailedException::class);

it('handles invoicing auth errors properly', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    IntegrationProvider $provider,
    string $fixtureName,
) {
    $payload = $fixture->response()->invoice()->files($fixtureName);

    Http::fake(mockResponse($provider, $payload, 401));

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('invoice-full', 'providers', ['invoice_auth'])
    ->throws(UnauthorizedException::class);

it('handles invoicing catastrophic errors', function (
    CreateInvoice $invoice,
    IntegrationProvider $provider,
) {
    Http::fake(mockResponse($provider, [], 500));

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->execute();
})->with('invoice', 'providers')
    ->throws(FailedReachingProviderException::class);
