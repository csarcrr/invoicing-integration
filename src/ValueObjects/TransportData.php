<?php

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use function collect;

class TransportData extends Data implements DataNeedsValidation
{
    public function __construct(
        public AddressData          $origin,
        public AddressData          $destination,
        public Optional|null|string $vehicleLicensePlate
    )
    {

    }

    public static function make (array $data) : self {
        TransportData::validate(collect($data)->map(function (mixed $item) {
            return $item instanceof Data ? $item->toArray() : $item;
        }));

        return TransportData::from($data);
    }
}
