<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

use Illuminate\Support\Facades\Validator;

trait HasEmail
{
    protected ?string $email = null;

    public function email(string $email): self
    {
        Validator::make(['email' => $email], [
            'email' => 'required|email',
        ])->validate();

        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
