<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Client;

use Illuminate\Support\Str;

trait HasName
{
    protected ?string $name = null;

    public function name(string $name): self
    {
        $name = Str::of($name)->ascii()->squish()->toString();

        if (empty($name)) {
            return $this;
        }

        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
