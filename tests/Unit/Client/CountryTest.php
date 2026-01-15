<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;

it('fails when country is invalid', function (CreateClient $client, string $invalidCountry) {
    $client->country($invalidCountry);
})->with('client', [
    ['PURTUGALE'],
])->throws(InvalidCountryException::class);
