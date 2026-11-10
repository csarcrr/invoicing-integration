<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Category implements ShouldFindCategory
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, CategoryData> */
    protected Collection $list;

    public function __construct(protected ?CategoryData $category = null)
    {
        $this->payload = collect();
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->post('productCategories/getAll', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->totalPages(1);
        $this->list = collect($results)->map(fn (array $category) => $this->toCategoryData($category))->values();

        return $this;
    }

    /**
     * @return Collection<int, CategoryData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->payload->put('parent_id', $this->category?->parentId ?? 0);

        return $this->payload;
    }
}
