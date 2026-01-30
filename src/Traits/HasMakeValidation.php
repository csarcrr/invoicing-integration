<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Spatie\LaravelData\Data;

use function collect;

trait HasMakeValidation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        $normalizedData = collect($data)
            ->map(fn (mixed $item) => $item instanceof Data ? $item->toArray() : $item)
            ->all();

        return static::validateAndCreate($normalizedData);
    }
}
