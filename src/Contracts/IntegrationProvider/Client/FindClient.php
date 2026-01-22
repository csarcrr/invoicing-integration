<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use Illuminate\Support\Collection;

interface FindClient
{
    public function execute(): self;

    /**
     * @return Collection<int, mixed>
     */
    public function getList(): Collection;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
