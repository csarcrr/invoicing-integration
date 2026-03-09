<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use Spatie\LaravelData\Data;

/**
 * Base class for Cegid Vendus client operations, handling shared client data and property mapping.
 */
#[AllowDynamicProperties]
class Client extends Base
{
    protected ?ClientData $client = null;

    /**
     * Returns the current client DTO after an operation has been executed.
     */
    public function getClient(): ClientData
    {
        return $this->client;
    }
}
