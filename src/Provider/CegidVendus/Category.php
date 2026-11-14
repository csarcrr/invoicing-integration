<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

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
        return CategoryData::make([
            'id' => (int) $category['id'],
            'name' => $category['title'],
        ]);
    }
}
