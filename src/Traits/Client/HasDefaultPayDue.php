<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasDefaultPayDue
{
    protected ?int $defaultPayDue = null;

    public function defaultPayDue(int $defaultPayDue): self
    {
        $this->defaultPayDue = $defaultPayDue;

        return $this;
    }

    public function getDefaultPayDue(): ?int
    {
        return $this->defaultPayDue;
    }
}
