<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use Illuminate\Validation\ValidationException;

it('throws error when email is invalid', function (Provider $provider, string $invalidEmail) {
    ClientData::email($invalidEmail);
})->with('providers', [
    ['invalid'],
    ['email@'],
])->throws(ValidationException::class);
