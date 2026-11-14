<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use Illuminate\Validation\ValidationException;

it('requires a name', function () {
    TaxData::make([]);
})->throws(ValidationException::class);

it('defaults every other field to null', function () {
    $tax = TaxData::make(['name' => 'Isento']);

    expect($tax->id)->toBeNull()
        ->and($tax->rate)->toBeNull()
        ->and($tax->type)->toBeNull()
        ->and($tax->fiscalZone)->toBeNull()
        ->and($tax->exemptionReason)->toBeNull();
});

it('accepts a tax type and an exemption reason', function () {
    $tax = TaxData::make([
        'name' => 'Isento',
        'type' => ItemTax::EXEMPT,
        'exemptionReason' => 'M07',
    ]);

    expect($tax->type)->toBe(ItemTax::EXEMPT)
        ->and($tax->exemptionReason)->toBe('M07');
});
