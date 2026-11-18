<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;

it('defaults every field to null', function () {
    $category = CategoryData::make([]);

    expect($category->id)->toBeNull()
        ->and($category->name)->toBeNull()
        ->and($category->parentId)->toBeNull();
});
