<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;

interface GetClient
{
    public function execute(): ClientDataObject;
}
