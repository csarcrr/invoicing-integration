<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Illuminate\Support\Collection;

trait HasPayload
{
    protected Collection $payload;

    public function payload(array|Collection $payload): self
    {
        $this->payload = is_array($payload) ? collect($payload) : $payload;

        return $this;
    }

    public function getPayload(): Collection
    {
        return $this->payload;
    }
}
