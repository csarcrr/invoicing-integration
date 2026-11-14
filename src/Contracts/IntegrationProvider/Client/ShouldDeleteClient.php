<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

interface ShouldDeleteClient
{
    public function execute(): void;
}
