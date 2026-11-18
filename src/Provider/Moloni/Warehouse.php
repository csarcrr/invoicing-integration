<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Provider\Base;

use function collect;

/** @extends Base<WarehouseData> */
abstract class Warehouse extends Base
{
    /**
     * @param  array<string, mixed>  $warehouse
     */
    protected function toWarehouseData(array $warehouse): WarehouseData
    {
        return WarehouseData::make([
            'id' => (int) $warehouse['warehouse_id'],
            'name' => $warehouse['title'],
        ])->additional(collect($warehouse)->except(['warehouse_id', 'title'])->toArray());
    }
}
