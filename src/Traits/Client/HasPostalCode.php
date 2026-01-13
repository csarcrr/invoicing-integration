<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasPostalCode
{
    protected string $postalCode;

    public function postalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
}
