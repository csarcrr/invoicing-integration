<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Vendus\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFetchCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use Illuminate\Support\Facades\Http;

class Fetch implements ShouldFetchCategory
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

    public function get(): CategoryData
    {
        return $this->category;
    }
}
