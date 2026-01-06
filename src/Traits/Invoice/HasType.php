<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;

trait HasType
{
    protected InvoiceType $type = InvoiceType::Invoice;

    public function type(InvoiceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): InvoiceType
    {
        return $this->type;
    }
}
