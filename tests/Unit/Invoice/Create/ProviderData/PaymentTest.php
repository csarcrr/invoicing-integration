<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetPaymentMethodIdException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    Carbon::setTestNow('2025-06-15');
});

it('transforms to provider payload with single payment', function (Provider $provider, string $fixtureName) {
    fakeProviderPaymentMethods($provider);

    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::InvoiceReceipt,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
            ],
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['payment']);

it('transforms to provider payload with multiple payments', function (Provider $provider, string $fixtureName) {
    fakeProviderPaymentMethods($provider);

    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::InvoiceReceipt,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::MONEY]),
            ],
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['payment_multiple']);

it('fetches the provider payment methods once per invoice', function (Provider $provider) {
    fakeProviderPaymentMethods($provider);

    Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::InvoiceReceipt,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::MONEY]),
            ],
        ])
    )->getPayload();

    if ($provider === Provider::CEGID_VENDUS) {
        Http::assertNothingSent();

        return;
    }

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'paymentMethods/getAll')
        && Str::contains($request->body(), 'qty=50')
        && Str::contains($request->body(), 'offset=0'));
})->with('providers');

it('only sends payments on document types that accept them', function (Provider $provider) {
    $invoice = Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::Invoice,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
            ],
        ])
    );

    $payload = $invoice->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload)->toHaveKey('payments'),
        Provider::MOLONI => expect($payload)->not->toHaveKey('payments'),
    };

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'paymentMethods/getAll'));
})->with('providers');

it('throws error when the payment method is not available', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        config()->set('invoicing-integration.providers.'.$provider->value.'.payments', [
            PaymentMethod::CREDIT_CARD->value => null,
            PaymentMethod::MONEY->value => null,
            PaymentMethod::MB->value => null,
            PaymentMethod::MONEY_TRANSFER->value => null,
            PaymentMethod::CURRENT_ACCOUNT->value => null,
        ]);
    }

    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse([]));
    }

    $invoice = Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::InvoiceReceipt,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD])],
        ])
    );

    match ($provider) {
        Provider::CEGID_VENDUS => expect(fn () => $invoice->getPayload())
            ->toThrow(Exception::class, 'Payment method not configured.'),
        Provider::MOLONI => expect(fn () => $invoice->getPayload())
            ->toThrow(CouldNotGetPaymentMethodIdException::class),
    };
})->with('providers');

it('throws error when the provider has no payment method of that type', function (Provider $provider, PaymentMethod $method) {
    fakeProviderPaymentMethods($provider);

    $invoice = Invoice::create(
        InvoiceData::make([
            'type' => InvoiceType::InvoiceReceipt,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [PaymentData::from(['amount' => 500, 'method' => $method])],
        ])
    );

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getPayload())->toHaveKey('payments'),
        Provider::MOLONI => expect(fn () => $invoice->getPayload())
            ->toThrow(CouldNotGetPaymentMethodIdException::class),
    };
})->with('providers')->with([
    PaymentMethod::MONEY_TRANSFER,
    PaymentMethod::CURRENT_ACCOUNT,
]);
