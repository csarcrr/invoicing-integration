<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\DueDateAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldCreateDueDate;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateDueDate create(DueDateData $dueDate)
 * @method static ShouldFindDueDate find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\DueDateAction
 */
class DueDate extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return DueDateAction::class;
    }
}
