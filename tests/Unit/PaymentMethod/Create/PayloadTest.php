<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

it('transforms payment method data to provider payload', function () {
    $payload = PaymentMethod::create(PaymentMethodData::make([
        'name' => 'Multibanco',
        'type' => PaymentMethodEnum::MB,
    ]))->getPayload()->toArray();

    expect($payload)->toMatchArray(fixtures()->request()->paymentMethod()->files('create'));
});

it('maps the payment method type to the provider flags', function (?PaymentMethodEnum $type, array $flags) {
    expect(PaymentMethod::create(PaymentMethodData::make(['name' => 'Método', 'type' => $type]))->getPayload()->toArray())
        ->toMatchArray($flags);
})->with([
    'money' => [PaymentMethodEnum::MONEY, ['is_numerary' => 1, 'is_mb' => 0, 'is_credit' => 0]],
    'mb' => [PaymentMethodEnum::MB, ['is_numerary' => 0, 'is_mb' => 1, 'is_credit' => 0]],
    'credit card' => [PaymentMethodEnum::CREDIT_CARD, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 1]],
    'money transfer' => [PaymentMethodEnum::MONEY_TRANSFER, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
    'current account' => [PaymentMethodEnum::CURRENT_ACCOUNT, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
    'no type' => [null, ['is_numerary' => 0, 'is_mb' => 0, 'is_credit' => 0]],
]);
