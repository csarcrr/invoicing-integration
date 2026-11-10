<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use CsarCrr\InvoicingIntegration\Data\TaxData;

/**
 * @extends Base<TaxData>
 */
class Tax extends Base
{
    public function getTax(): TaxData
    {
        return $this->data;
    }
}
