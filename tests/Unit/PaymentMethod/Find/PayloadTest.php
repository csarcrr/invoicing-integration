<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;

it('builds the pagination payload for the first page', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support finding payment methods.');
    }

    expect(PaymentMethod::find()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 0]);
})->with('providers');

it('moves the offset when changing page', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support finding payment methods.');
    }

    expect(PaymentMethod::find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]);
})->with('providers');
