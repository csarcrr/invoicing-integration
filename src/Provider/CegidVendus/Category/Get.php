<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use Illuminate\Support\Facades\Http;

class Get implements ShouldGetCategory
{
    public function __construct(protected CategoryData $category) {}

    public function execute(): self
    {
        throw_if(! is_int($this->category->id), \InvalidArgumentException::class, 'Category ID is required.');

        $request = Http::provider()->get("products/categories/{$this->category->id}");

        Http::handleUnwantedFailures($request);

        /** @var array<string, mixed> $data */
        $data = $request->json();

        $this->category = CategoryData::make([
            'id' => $data['id'],
            'name' => $data['title'],
        ]);

        return $this;
    }

    public function getCategory(): CategoryData
    {
        return $this->category;
    }
}
