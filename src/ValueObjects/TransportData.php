<?php

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TransportData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public AddressData $origin,
        public AddressData $destination,
        public Optional|null|string $vehicleLicensePlate
    ) {}
}
