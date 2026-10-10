<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('creates a payment method', function (Provider $provider) {
    $create = fn () => PaymentMethod::create(PaymentMethodData::make([
        'name' => 'Multibanco',
        'type' => PaymentMethodEnum::MB,
    ]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($create)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('create')));

    $paymentMethod = $create()->execute()->getPaymentMethod();

    expect($paymentMethod->id)->toBe(7002)
        ->and($paymentMethod->name)->toBe('Multibanco')
        ->and($paymentMethod->type)->toBe(PaymentMethodEnum::MB)
        ->and($paymentMethod->getAdditionalData())->toBe(['valid' => 1]);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'paymentMethods/insert')
        && Str::contains($request->body(), 'is_mb=1'));
})->with('providers');
