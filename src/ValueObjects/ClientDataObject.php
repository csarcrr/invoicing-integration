<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

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

class ClientDataObject
{
    use HasAddress;
    use HasCity;
    use HasCountry;
    use HasDefaultPayDue;
    use HasEmail;
    use HasEmailNotification;
    use HasIrsRetention;
    use HasName;
    use HasNotes;
    use HasPhone;
    use HasPostalCode;
    use HasVat;

    protected ?int $id = null;

    public function __construct() {}

    public function id(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
