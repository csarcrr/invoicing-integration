<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\CompanyAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company\ShouldFindCompany;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindCompany find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\CompanyAction
 */
class Company extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return CompanyAction::class;
    }
}
