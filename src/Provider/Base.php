<?php

namespace CsarCrr\InvoicingIntegration\Provider;

use Spatie\LaravelData\Data;

use function collect;

abstract class Base
{
    protected array $supportedProperties = [];

    /**
     * Fills properties that are not supported.
     *
     * @param  array<string, mixed>  $values
     *
     * @throws \Throwable
     */
    protected function fillAdditionalProperties(array $values, Data $data): void
    {
        $additionalProperties = collect($values)->except($this->supportedProperties)->toArray();
        $data->additional($additionalProperties);
    }
}
