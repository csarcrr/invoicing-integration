<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasIrsRetention
{
    protected bool $irsRetention = false;

    public function irsRetention(bool $irsRetention): self
    {
        $this->irsRetention = $irsRetention;

        return $this;
    }

    public function getIrsRetention(): bool
    {
        return $this->irsRetention;
    }
}
