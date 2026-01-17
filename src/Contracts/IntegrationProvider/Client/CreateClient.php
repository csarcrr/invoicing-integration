<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Collection;

interface CreateClient
{
    public function execute(): ClientData;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
