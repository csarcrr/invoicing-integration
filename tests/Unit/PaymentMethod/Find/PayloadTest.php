<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

it('builds the pagination payload for the first page', function (Provider $provider) {
    $find = fn () => PaymentMethod::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 0]),
    };
})->with('providers');

it('moves the offset when changing page', function (Provider $provider) {
    $find = fn () => PaymentMethod::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]),
    };
})->with('providers');
