<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Unit;

it('is an instance of the correct when finding units', function (Provider $provider) {
    expect(Unit::find())->toBeInstanceOf(ShouldFindUnit::class);
})->with('providers');
