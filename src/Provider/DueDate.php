<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use CsarCrr\InvoicingIntegration\Data\DueDateData;

/**
 * @extends Base<DueDateData>
 */
class DueDate extends Base
{
    public function getDueDate(): DueDateData
    {
        return $this->data;
    }
}
