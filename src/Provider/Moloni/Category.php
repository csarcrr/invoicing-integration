<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Provider\Base;

/** @extends Base<CategoryData> */
abstract class Category extends Base
{
    /**
     * @param  array<string, mixed>  $category
     */
    protected function toCategoryData(array $category): CategoryData
    {
        $parentId = (int) ($category['parent_id'] ?? 0);

        return CategoryData::make([
            'id' => (int) $category['category_id'],
            'name' => $category['name'],
            'parentId' => $parentId === 0 ? null : $parentId,
        ]);
    }
}
