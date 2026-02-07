<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Spatie\LaravelData\Data;

use function call_user_func;
use function collect;

trait HasMakeValidation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        $normalizedData = collect($data)
            ->map(fn(mixed $item) => self::transformItemToArray($item))
            ->map(function (mixed $item) {
                // nested Data instances must be transformed to arrays, or the collector does not accept them
                // this only works for 1 level of nesting
                if(!is_array($item)){
                    return $item;
                }

                return collect($item)->map(fn(mixed $item) => self::transformItemToArray($item))->toArray();
            })->all();

        return static::validateAndCreate($normalizedData);
    }

    protected static function transformItemToArray(mixed $item): mixed
    {
        return $item instanceof Data ? $item->toArray() : $item;
    }
}
