<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use Illuminate\Support\Collection;

/**
 * Base class for Cegid Vendus item operations, holding item data and supported API response properties.
 *
 * @extends Base<ItemData>
 */
#[AllowDynamicProperties]
abstract class Item extends Base
{
    /**
     * @return Collection<string, mixed>
     */
    abstract public function getConfig(): Collection;

    /**
     * Returns the current item DTO after an operation has been executed.
     */
    public function getItem(): ItemData
    {
        return $this->data;
    }

    protected function toUnit(mixed $unitId): ?Unit
    {
        if (empty($unitId)) {
            return null;
        }

        /** @var array<string, mixed> $units */
        $units = $this->getConfig()->get('units', []);

        $unitKey = collect($units)->search(fn (mixed $configured): bool => (int) $configured === (int) $unitId);

        return $unitKey === false ? null : Unit::tryFrom($unitKey);
    }
}
