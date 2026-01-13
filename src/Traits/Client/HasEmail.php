<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasEmail
{
    protected string $email;

    public function email(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
