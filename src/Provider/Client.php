<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ClientData;

/**
 * Base class for Cegid Vendus client operations, handling shared client data and property mapping.
 *
 * @extends Base<ClientData>
 */
#[AllowDynamicProperties]
class Client extends Base
{
    /**
     * Returns the current client DTO after an operation has been executed.
     */
    public function getClient(): ClientData
    {
        return $this->data;
    }
}
