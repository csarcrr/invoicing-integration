<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use Illuminate\Validation\ValidationException;

it('throws error when email is invalid', function (IntegrationProvider $provider, string $invalidEmail) {
    ClientData::email($invalidEmail);
})->with('providers', [
    ['invalid'],
    ['email@'],
])->throws(ValidationException::class);
