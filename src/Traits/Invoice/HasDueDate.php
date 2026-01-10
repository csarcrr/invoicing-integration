<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use Carbon\Carbon;

trait HasDueDate
{
    protected ?Carbon $dueDate = null;

    public function dueDate(Carbon $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getDueDate(): ?Carbon
    {
        return $this->dueDate;
    }
}
