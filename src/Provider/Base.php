<?php

namespace CsarCrr\InvoicingIntegration\Provider;

use Spatie\LaravelData\Data;

use function collect;

/**
 * Abstract base class for provider implementations, providing shared payload utilities.
 */
abstract class Base
{
    /**
     * @var list<string>
     */
    protected array $supportedProperties = [];

    /**
     * Stores response fields not listed in `$supportedProperties` as additional data on the given DTO.
     *
     * @param  array<string, mixed>  $values
     * @param  Data  $data
     *
     * @throws \Throwable
     */
    protected function fillAdditionalProperties(array $values, Data $data): void
    {
        $additionalProperties = collect($values)->except($this->supportedProperties)->toArray();
        $data->additional($additionalProperties);
    }
}
