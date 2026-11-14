<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
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
        $tax = ItemTax::tryFrom($item['tax']['id'] ?? '');
        $exemptionReason = $tax === ItemTax::EXEMPT
            ? (($item['tax']['exemption'] ?? '') ?: null)
            : null;

        return ItemData::make([
            'id' => $id,
            'name' => $item['title'] ?? null,
            'description' => $item['description'] ?? null,
            'reference' => $item['reference'] ?? null,
            'barcode' => $item['barcode'] ?? null,
            'category' => CategoryData::make(['id' => isset($item['category_id']) ? (int) $item['category_id'] : null]),
            'unit' => $this->toUnit($item['unit_id'] ?? null),
            'controlStock' => (bool) ($item['stock']['control'] ?? true),
            'enabled' => $this->toEnabled($item['status'] ?? null),
            'type' => match ($item['type_id'] ?? null) {
                'P' => ItemType::Product,
                'S' => ItemType::Service,
                'O' => ItemType::Other,
                'I' => ItemType::Tax,
                'E' => ItemType::SpecialTax,
                default => null,
            },
            'tax' => $tax,
            'taxExemptionReason' => $exemptionReason,
            'taxExemptionLaw' => $exemptionReason ? (($item['tax']['exemption_law'] ?? '') ?: null) : null,
            'price' => isset($item['prices']['gross']) ? $this->toCents((float) $item['prices']['gross']) : null,
            'stock' => array_map(fn (array $store) => [
                'itemId' => $id,
                'warehouseId' => $store['store_id'],
                'quantity' => $store['stock'],
            ], $item['stock']['stores'] ?? []),
        ]);
    }

    protected function toEnabled(?string $status): ?bool
    {
        return match ($status) {
            'on' => true,
            'off' => false,
            default => null,
        };
    }
}
