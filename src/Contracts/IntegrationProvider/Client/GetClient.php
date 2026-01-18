<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

interface GetClient
{
    public function execute(): ClientData;
}
