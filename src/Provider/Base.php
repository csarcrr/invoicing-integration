<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

use function collect;

/**
 * Abstract base class for provider implementations, providing shared payload utilities.
 */
abstract class Base
{
    protected Data $data;

    /**
     * @var array<string>
     */
    protected array $supportedProperties;

    /**
     * Stores response fields not listed in `$supportedProperties` as additional data on the given DTO.
     *
     * @param  array<string, mixed>  $values
     */
    protected function fillAdditionalProperties(array $values, Data $data): void
    {
        $additionalProperties = collect($values)->except($this->supportedProperties)->toArray();
        $data->additional($additionalProperties);
    }

    /**
     * @return Collection<string, mixed>
     */
    protected function getAllowedProperties(Data $data): Collection
    {
        return collect($data->toArray())->filter(
            fn (mixed $value, string $key) => in_array($key, $this->supportedProperties)
        );
    }
}
