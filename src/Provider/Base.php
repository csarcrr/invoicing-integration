<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

use function collect;

/**
 * Abstract base class for provider implementations, providing shared payload utilities.
 *
 * @template TData of Data
 */
abstract class Base
{
    /** @var TData */
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
    protected function fillAdditionalProperties(array $values): void
    {
        $additionalProperties = collect($values)->except($this->supportedProperties)->toArray();
        $this->data->additional($additionalProperties);
    }

    /**
     * @return Collection<string, mixed>
     */
    protected function getAllowedProperties(): Collection
    {
        return collect($this->data->toArray())->filter(
            fn (mixed $value, string $key) => in_array($key, $this->supportedProperties)
        );
    }
}
