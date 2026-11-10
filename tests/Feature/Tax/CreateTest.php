<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Tax;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('creates a tax', function () {
    Http::fake(mockResponse(fixtures()->response()->tax()->files('create')));

    $tax = Tax::create(TaxData::make([
        'name' => 'Isento',
        'rate' => 0,
        'type' => ItemTax::EXEMPT,
        'fiscalZone' => 'PT',
        'exemptionReason' => TaxExemptionReason::M07,
    ]))->execute()->getTax();

    expect($tax->id)->toBe(3951900)
        ->and($tax->name)->toBe('Isento')
        ->and($tax->type)->toBe(ItemTax::EXEMPT)
        ->and($tax->exemptionReason)->toBe(TaxExemptionReason::M07)
        ->and($tax->getAdditionalData())->toBe(['valid' => 1]);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'taxes/insert')
        && Str::contains($request->body(), 'vat_type=ISE')
        && Str::contains($request->body(), 'exemption_reason=M07'));
});
