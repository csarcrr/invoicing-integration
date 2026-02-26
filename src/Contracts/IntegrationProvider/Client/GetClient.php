<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Data\ClientData;

interface GetClient
{
    public function execute(): self;

    public function getClient(): ClientData;
}
