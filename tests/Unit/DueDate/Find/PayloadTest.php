<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

it('builds the pagination payload for the first page', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support finding due dates.');
    }

    expect(DueDate::find()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 0]);
})->with('providers');

it('moves the offset when changing page', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support finding due dates.');
    }

    expect(DueDate::find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]);
})->with('providers');
