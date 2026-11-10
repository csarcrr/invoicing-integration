<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

it('builds the pagination payload for the first page', function (Provider $provider) {
    expect(PaymentMethod::find()->getPayload()->toArray())->toBe(match ($provider) {
        Provider::CEGID_VENDUS => [],
        Provider::MOLONI => ['qty' => 50, 'offset' => 0],
    });
})->with('providers');

it('moves the offset when changing page on Moloni', function () {
    mockConfiguration(Provider::MOLONI);

    expect(PaymentMethod::find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]);
});
