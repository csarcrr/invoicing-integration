<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasVat
{
    protected string|int $vat;

    public function vat(string|int $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVat(): string|int
    {
        return $this->vat;
    }
}
