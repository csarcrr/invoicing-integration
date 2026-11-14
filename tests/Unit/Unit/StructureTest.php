<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldCreateUnit;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Unit;

it('is an instance of the correct when finding units', function (Provider $provider) {
    expect(Unit::find())->toBeInstanceOf(ShouldFindUnit::class);
})->with('providers');

it('is an instance of the correct when creating a unit', function () {
    expect(Unit::create(UnitData::make(['name' => 'Kilograma'])))->toBeInstanceOf(ShouldCreateUnit::class);
})->with('providers');
