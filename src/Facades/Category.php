<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\CategoryAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindCategory find(?CategoryData $category = null)
 * @method static ShouldGetCategory get(CategoryData $category)
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\CategoryAction
 */
class Category extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return CategoryAction::class;
    }
}
