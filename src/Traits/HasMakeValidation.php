<?php
declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportData;
use Spatie\LaravelData\Data;
use function collect;

/**
 * @method static validateAndCreate(array $data)
 * @method static validate(\Illuminate\Support\Collection $map)
 */
trait HasMakeValidation
{
    public static function make(array $data): self {
        $normalizedData = collect($data)->map(function (mixed $item) {
            return $item instanceof Data ? $item->toArray() : $item;
        });

        self::validate($normalizedData);

        return self::from($data);
    }
}
