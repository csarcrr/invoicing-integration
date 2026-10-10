<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

it('transforms payment method data to provider payload', function (Provider $provider) {
    $create = fn () => PaymentMethod::create(PaymentMethodData::make([
        'name' => 'Multibanco',
        'type' => PaymentMethodEnum::MB,
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create()->getPayload()->toArray())
            ->toMatchArray(fixtures()->request()->paymentMethod()->files('create')),
    };
})->with('providers');

it('maps the payment method type to the provider flags', function (Provider $provider, ?PaymentMethodEnum $type, array $flags) {
    $create = fn () => PaymentMethod::create(PaymentMethodData::make(['name' => 'Método', 'type' => $type]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create()->getPayload()->toArray())->toMatchArray($flags),
    };
})->with('providers')->with([
    'money' => [PaymentMethodEnum::MONEY, ['is_numerary' => 1, 'is_mb' => 0, 'is_credit' => 0]],
    'mb' => [PaymentMethodEnum::MB, ['is_numerary' => 0, 'is_mb' => 1, 'is_credit' => 0]],
    'credit card' => [PaymentMethodEnum::CREDIT_CARD, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 1]],
    'money transfer' => [PaymentMethodEnum::MONEY_TRANSFER, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
    'current account' => [PaymentMethodEnum::CURRENT_ACCOUNT, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
    'no type' => [null, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
]);
