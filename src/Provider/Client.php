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
    /**
     * Returns the current client DTO after an operation has been executed.
     * @return ClientData
     */
    public function getClient(): Data
    {
        return $this->data;
    }
}
