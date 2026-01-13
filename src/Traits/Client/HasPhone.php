<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasPhone
{
    protected string $phone;

    public function phone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
