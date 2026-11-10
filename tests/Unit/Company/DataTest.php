<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CompanyData;
use Illuminate\Validation\ValidationException;

it('requires a name', function () {
    CompanyData::make([]);
})->throws(ValidationException::class);

it('defaults the id and the vat to null', function () {
    $company = CompanyData::make(['name' => 'Empresa Demonstração']);

    expect($company->id)->toBeNull()
        ->and($company->vat)->toBeNull();
});
