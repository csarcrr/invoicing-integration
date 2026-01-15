<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('has the correct country', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->country('PT');

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['country']);

it('fails when country is invalid', function (CreateClient $client, string $invalidCountry) {
    $client->country($invalidCountry);
})->with('client', [
    ['PURTUGALE'],
])->throws(InvalidCountryException::class);
