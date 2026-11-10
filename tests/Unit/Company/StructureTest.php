<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company\ShouldFindCompany;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\Company;

it('is an instance of the correct when finding companies', function (Provider $provider) {
    $find = fn () => Company::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find())->toBeInstanceOf(ShouldFindCompany::class),
    };
})->with('providers');
