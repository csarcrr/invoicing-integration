<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ShouldCreateClient
{
    public function execute(): self;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;

    /**
     * @return ClientData
     */
    public function getClient(): Data;
}
