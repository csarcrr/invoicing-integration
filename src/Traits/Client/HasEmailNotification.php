<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

trait HasEmailNotification
{
    protected bool $emailNotification = false;

    public function emailNotification(bool $emailNotification): self
    {
        $this->emailNotification = $emailNotification;

        return $this;
    }

    public function getEmailNotification(): bool
    {
        return $this->emailNotification;
    }
}
