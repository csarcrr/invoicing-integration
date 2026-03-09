<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Spatie\LaravelData\Data;

interface ShouldGetClient
{
    public function execute(): self;

    /**
     * @return ClientData
     */
    public function getClient(): Data;
}
