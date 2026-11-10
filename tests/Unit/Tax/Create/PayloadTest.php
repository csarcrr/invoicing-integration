<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Tax;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

it('transforms tax data to provider payload', function () {
    $payload = Tax::create(TaxData::make([
        'name' => 'Isento',
        'rate' => 0,
        'type' => ItemTax::EXEMPT,
        'fiscalZone' => 'PT',
        'exemptionReason' => TaxExemptionReason::M07,
    ]))->getPayload()->toArray();

    expect($payload)->toMatchArray(fixtures()->request()->tax()->files('create'));
});

it('maps the tax type to the provider vat type', function (ItemTax $type) {
    expect(Tax::create(TaxData::make(['name' => 'Taxa', 'type' => $type]))->getPayload()->get('vat_type'))
        ->toBe($type->value);
})->with(ItemTax::cases());

it('does not send the fields that are not set', function () {
    $payload = Tax::create(TaxData::make(['name' => 'Taxa']))->getPayload()->toArray();

    expect($payload)->not->toHaveKeys(['value', 'vat_type', 'fiscal_zone'])
        ->and($payload['exemption_reason'])->toBe('');
});
