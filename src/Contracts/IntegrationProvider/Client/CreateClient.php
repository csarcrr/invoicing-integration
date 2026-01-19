<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Collection;

interface CreateClient
{
    public function execute(): ClientDataObject;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
