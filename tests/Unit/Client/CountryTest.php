<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('fails when country is invalid', function (IntegrationProvider $provider, Fixtures $fixtures, string $invalidCountry) {
    (new ClientData)->country($invalidCountry);
})->with('client-full', [
    ['PURTUGALE'],
])->throws(InvalidCountryException::class);
