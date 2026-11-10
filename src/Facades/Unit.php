<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\UnitAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindUnit find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\UnitAction
 */
class Unit extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return UnitAction::class;
    }
}
