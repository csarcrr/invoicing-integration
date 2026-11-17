<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

it('builds the payload for the first page', function (Provider $provider) {
    expect(DueDate::find()->getPayload()->toArray())->toBe(match ($provider) {
        Provider::VENDUS => [],
        Provider::MOLONI => ['qty' => 50, 'offset' => 0],
    });
})->with('providers');

it('moves the offset when changing page', function () {
    mockConfiguration(Provider::MOLONI);

    expect(DueDate::find()->next()->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 50]);
});
