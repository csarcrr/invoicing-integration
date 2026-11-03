<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;

trait ResolvesTaxConfig
{
    /**
     * @return array{id: int, rate: float}|null
     *
     * @throws CouldNotGetTaxIdException
     */
    protected function taxConfig(?ItemTax $itemTax): ?array
    {
        if (! $itemTax) {
            return null;
        }

        $tax = $this->getConfig()->get('taxes')[$itemTax->value] ?? null;

        throw_if(empty($tax['id']), CouldNotGetTaxIdException::class);

        return [
            'id' => (int) $tax['id'],
            'rate' => (float) ($tax['rate'] ?? 0),
        ];
    }
}
