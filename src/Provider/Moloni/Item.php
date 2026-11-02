<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Provider\Item as BaseItem;
use Illuminate\Support\Collection;

abstract class Item extends BaseItem
{
    /**
     * @return Collection<string, mixed>
     */
    abstract public function getConfig(): Collection;

    /**
     * @param  array<string, mixed>  $item
     */
    protected function toItemData(?int $id, array $item): ItemData
    {
        $tax = $item['taxes'][0] ?? null;

        return ItemData::make([
            'id' => $id,
            'name' => $item['name'] ?? null,
            'description' => $item['summary'] ?? null,
            'reference' => $item['reference'] ?? null,
            'barcode' => $item['ean'] ?? null,
            'category' => CategoryData::make(['id' => $item['category_id'] ?? null]),
            'controlStock' => (bool) ($item['has_stock'] ?? true),
            'type' => match ($item['type'] ?? null) {
                1 => ItemType::Product,
                2 => ItemType::Service,
                3 => ItemType::Other,
                default => null,
            },
            'tax' => $tax ? $this->toItemTax($tax) : null,
            'price' => $tax ? $this->toCents((float) $item['price']) + $this->toCents((float) $tax['value']) : null,
            'stock' => array_map(fn (array $warehouse) => [
                'itemId' => $warehouse['product_id'],
                'warehouseId' => $warehouse['warehouse_id'],
                'quantity' => $warehouse['stock'],
            ], $item['warehouses'] ?? []),
        ]);
    }

    /**
     * Resolves the tax by its configured Moloni tax id, falling back to the
     * Moloni vat_type for taxes that are not configured.
     *
     * @param  array<string, mixed>  $tax
     */
    protected function toItemTax(array $tax): ?ItemTax
    {
        /** @var array<string, array{id: mixed}> $configuredTaxes */
        $configuredTaxes = $this->getConfig()->get('taxes', []);

        $configuredKey = collect($configuredTaxes)
            ->search(fn (array $configured): bool => (int) $configured['id'] === (int) $tax['tax_id']);

        if ($configuredKey !== false) {
            return ItemTax::from($configuredKey);
        }

        return ItemTax::tryFrom((string) $tax['tax']['vat_type']);
    }
}
