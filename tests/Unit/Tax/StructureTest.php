<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldCreateTax;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldFindTax;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\Tax;

it('is an instance of the correct when creating a tax', function (Provider $provider) {
    $create = fn () => Tax::create(TaxData::make(['name' => 'Isento']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create())->toBeInstanceOf(ShouldCreateTax::class),
    };
})->with('providers');

it('is an instance of the correct when finding taxes', function (Provider $provider) {
    $find = fn () => Tax::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find())->toBeInstanceOf(ShouldFindTax::class),
    };
})->with('providers');
