<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Traits\Client\HasAddress;
use CsarCrr\InvoicingIntegration\Traits\Client\HasCity;
use CsarCrr\InvoicingIntegration\Traits\Client\HasCountry;
use CsarCrr\InvoicingIntegration\Traits\Client\HasDefaultPayDue;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmail;
use CsarCrr\InvoicingIntegration\Traits\Client\HasEmailNotification;
use CsarCrr\InvoicingIntegration\Traits\Client\HasIrsRetention;
use CsarCrr\InvoicingIntegration\Traits\Client\HasName;
use CsarCrr\InvoicingIntegration\Traits\Client\HasNotes;
use CsarCrr\InvoicingIntegration\Traits\Client\HasPhone;
use CsarCrr\InvoicingIntegration\Traits\Client\HasPostalCode;
use CsarCrr\InvoicingIntegration\Traits\Client\HasVat;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use CsarCrr\InvoicingIntegration\Traits\HasPayload;

class Create implements CreateClient, ShouldHaveConfig, ShouldHavePayload
{
    use HasAddress;
    use HasCity;
    use HasConfig;
    use HasCountry;
    use HasDefaultPayDue;
    use HasEmail;
    use HasEmailNotification;
    use HasIrsRetention;
    use HasName;
    use HasNotes;
    use HasPayload;
    use HasPhone;
    use HasPostalCode;
    use HasVat;

    public function __construct(array $config)
    {
        $this->config($config);
    }
}
