<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\DueDateData;

interface ShouldCreateDueDate extends ShouldExecute, ShouldHavePayload
{
    public function getDueDate(): DueDateData;
}
