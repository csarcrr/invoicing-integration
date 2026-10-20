<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldCreatePaymentMethod;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

it('is an instance of the correct when creating a payment method', function (Provider $provider) {
    $create = fn () => PaymentMethod::create(PaymentMethodData::make(['name' => 'Multibanco']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create())->toBeInstanceOf(ShouldCreatePaymentMethod::class),
    };
})->with('providers');

it('is an instance of the correct when finding payment methods', function (Provider $provider) {
    $find = fn () => PaymentMethod::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find())->toBeInstanceOf(ShouldFindPaymentMethod::class),
    };
})->with('providers');
