<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('fails when country is invalid', function (IntegrationProvider $provider, string $invalidCountry) {
    (new ClientData)->country($invalidCountry);
})->with('providers', [
    ['PURTUGALE'],
])->throws(InvalidCountryException::class);
