<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Facades\Http;

class Get extends Category implements ShouldGetCategory
{
    use DetectsNoData;

    public function __construct(protected CategoryData $category) {}

    public function execute(): self
    {
        throw_if(! is_int($this->category->id), \InvalidArgumentException::class, 'Category ID is required.');

        $request = Http::provider()->post('productCategories/getOne', ['category_id' => $this->category->id]);

        Http::handleUnwantedFailures($request);

        throw_if($this->hasNoData($request), RequestFailedException::class, 'Category not found.');

        /** @var array<string, mixed> $data */
        $data = $request->json();

        $this->category = $this->toCategoryData($data);

        return $this;
    }

    public function getCategory(): CategoryData
    {
        return $this->category;
    }
}
