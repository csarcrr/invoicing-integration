<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindCategory
{
    use DetectsNoData;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, CategoryData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->get('products/categories', $this->getPayload()->toArray());

        if ($this->hasNoData($request)) {
            $this->updatePaginationDetails($request);
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $category) => CategoryData::make([
            'id' => $category['id'],
            'name' => $category['title'],
        ]))->values();

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
        $this->payload->put('page', $this->getCurrentPage());

        return $this->payload;
    }

    protected function updatePaginationDetails(Response $response): void
    {
        $this->totalPages((int) $response->header('X-Paginator-Pages') ?: 1);
    }
}
