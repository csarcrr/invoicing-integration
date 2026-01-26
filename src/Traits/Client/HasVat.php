<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasVat
{
    protected string|int|null $vat = null;

    protected function vat(string|int $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVat(): string|int|null
    {
        return $this->vat;
    }
}
