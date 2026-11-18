<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFetchCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldUpdateCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Category;

it('is an instance of the correct when finding categories', function (Provider $provider) {
    expect(Category::find())->toBeInstanceOf(ShouldFindCategory::class);
})->with('providers');

it('is an instance of the correct when getting a category', function (Provider $provider) {
    expect(Category::fetch(CategoryData::make(['id' => 1])))->toBeInstanceOf(ShouldFetchCategory::class);
})->with('providers');

it('is an instance of the correct when updating a category', function () {
    expect(Category::update(CategoryData::make(['id' => 1])))->toBeInstanceOf(ShouldUpdateCategory::class);
})->with('providers');
