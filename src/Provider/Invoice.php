<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

/**
 * Base class for Vendus invoice operations, holding invoice data and supported API response properties.
 *
 * @extends Base<InvoiceData>
 */
#[AllowDynamicProperties]
class Invoice extends Base
{
    protected bool $draft = false;

    public function asDraft(): static
    {
        $this->draft = true;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function atcud(array $document): ?string
    {
        $atcud = (string) ($document['atcud'] ?? '');

        return $atcud === '' ? null : $atcud;
    }
}
