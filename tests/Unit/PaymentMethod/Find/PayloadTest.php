<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

it('builds the pagination payload for the first page', function () {
    expect(PaymentMethod::find()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 0]);
});

it('moves the offset when changing page', function () {
    expect(PaymentMethod::find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]);
});
