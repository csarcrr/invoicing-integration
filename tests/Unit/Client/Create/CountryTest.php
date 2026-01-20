<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Facades\ClientData;

it('fails when country is invalid', function (Provider $provider, string $invalidCountry) {
    ClientData::country($invalidCountry);
})->with('providers', [
    ['PURTUGALE'],
])->throws(InvalidCountryException::class);
