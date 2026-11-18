<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\TaxAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldCreateTax;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldFindTax;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateTax create(TaxData $tax)
 * @method static ShouldFindTax find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\TaxAction
 */
class Tax extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TaxAction::class;
    }
}
