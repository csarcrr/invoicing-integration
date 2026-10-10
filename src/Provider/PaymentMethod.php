<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;

/**
 * @extends Base<PaymentMethodData>
 */
class PaymentMethod extends Base
{
    public function getPaymentMethod(): PaymentMethodData
    {
        return $this->data;
    }
}
