<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;

interface ShouldFetchClient extends ShouldExecute, ShouldHavePayload
{
    public function get(): ClientData;

    public function getUrl(): string;
}
