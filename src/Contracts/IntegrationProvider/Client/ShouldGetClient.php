<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\ClientData;

interface ShouldGetClient extends ShouldExecute
{
    public function getClient(): ClientData;
}
