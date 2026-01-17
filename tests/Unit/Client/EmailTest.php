<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Validation\ValidationException;

it('throws error when email is invalid', function (IntegrationProvider $provider, Fixtures $fixtures, string $invalidEmail) {
    (new ClientData)->email($invalidEmail);
})->with('client-full', [
    ['invalid'],
    ['email@'],
])->throws(ValidationException::class);
