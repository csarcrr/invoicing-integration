<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use Illuminate\Validation\ValidationException;

it('has the correct email', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->email('alberto.albertino@aa.pt');

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['email']);

it('throws error when email is invalid', function (CreateClient $client, string $invalidEmail) {
    $client->email($invalidEmail);
})->with('client', [
    ['invalid'],
    ['email@']
])->throws(ValidationException::class);
