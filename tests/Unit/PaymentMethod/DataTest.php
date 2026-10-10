<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use Illuminate\Validation\ValidationException;

it('requires a name', function () {
    PaymentMethodData::make([]);
})->throws(ValidationException::class);

it('defaults the type and the id to null', function () {
    $paymentMethod = PaymentMethodData::make(['name' => 'Multibanco']);

    expect($paymentMethod->type)->toBeNull()
        ->and($paymentMethod->id)->toBeNull();
});

it('accepts a payment method type', function () {
    $paymentMethod = PaymentMethodData::make(['name' => 'Multibanco', 'type' => PaymentMethod::MB]);

    expect($paymentMethod->type)->toBe(PaymentMethod::MB);
});
