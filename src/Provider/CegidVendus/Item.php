<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\Item as BaseItem;

abstract class Item extends BaseItem
{
    /**
     * @param  array<string, mixed>  $item
     */
    protected function toItemData(?int $id, array $item): ItemData
    {
        return ItemData::make([
            'id' => $id,
            'name' => $item['title'] ?? null,
            'description' => $item['description'] ?? null,
            'reference' => $item['reference'] ?? null,
            'barcode' => $item['barcode'] ?? null,
            'stock' => array_map(fn (array $store) => [
                'itemId' => $id,
                'warehouseId' => $store['store_id'],
                'quantity' => $store['stock'],
            ], $item['stock']['stores'] ?? []),
        ]);
    }
}
