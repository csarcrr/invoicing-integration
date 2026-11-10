<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Category;

it('is an instance of the correct when finding categories', function (Provider $provider) {
    expect(Category::find())->toBeInstanceOf(ShouldFindCategory::class);
})->with('providers');

it('is an instance of the correct when getting a category', function (Provider $provider) {
    expect(Category::get(CategoryData::make(['id' => 1])))->toBeInstanceOf(ShouldGetCategory::class);
})->with('providers');
