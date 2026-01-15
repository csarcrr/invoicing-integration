<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use Illuminate\Validation\ValidationException;

it('throws error when email is invalid', function (CreateClient $client, string $invalidEmail) {
    $client->email($invalidEmail);
})->with('client', [
    ['invalid'],
    ['email@'],
])->throws(ValidationException::class);
