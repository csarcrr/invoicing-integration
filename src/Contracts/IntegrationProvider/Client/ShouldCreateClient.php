<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ShouldCreateClient extends ShouldHavePayload, ShouldExecute
{
    /**
     * @return ClientData
     */
    public function getClient(): Data;
}
