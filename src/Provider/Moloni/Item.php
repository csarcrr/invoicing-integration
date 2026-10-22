<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Provider\Item as BaseItem;

abstract class Item extends BaseItem
{
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
            'tax' => $tax ? ItemTax::from($tax['tax']['vat_type']) : null,
            'price' => $tax ? $this->toCents((float) $item['price']) + $this->toCents((float) $tax['value']) : null,
            'stock' => collect($item['warehouses'] ?? [])->map(fn (array $warehouse) => ItemStockData::make([
                'itemId' => $warehouse['product_id'],
                'warehouseId' => $warehouse['warehouse_id'],
                'quantity' => $warehouse['stock'],
            ])),
        ]);
    }
}
