<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

trait HasCreditNoteReason
{
    protected ?string $creditNoteReason = null;

    public function creditNoteReason(string $reason): self
    {
        $this->creditNoteReason = $reason;

        return $this;
    }

    public function getCreditNoteReason(): ?string
    {
        return $this->creditNoteReason;
    }
}
