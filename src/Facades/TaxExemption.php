<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\TaxExemptionAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\TaxExemption\ShouldFindTaxExemption;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindTaxExemption find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\TaxExemptionAction
 */
class TaxExemption extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TaxExemptionAction::class;
    }
}
