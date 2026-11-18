<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\UnitData;
use Illuminate\Validation\ValidationException;

it('requires a name', function () {
    UnitData::make([]);
})->throws(ValidationException::class);

it('defaults the id and the short name to null', function () {
    $unit = UnitData::make(['name' => 'Kg']);

    expect($unit->id)->toBeNull()
        ->and($unit->shortName)->toBeNull();
});
