<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends Item implements ShouldFindItem
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, ItemData> */
    protected Collection $list;

    public function __construct(protected ?ItemData $item = null)
    {
        $this->data = $item ?? ItemData::make([]);
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);
        $this->perPage = 50;
        $this->totalPages(null);
    }

    /**
     * @throws MissingCategoryException
     */
    public function execute(): self
    {
        $request = Http::provider()->post('products/getAll', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, ItemData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws MissingCategoryException
     */
    public function getPayload(): Collection
    {
        $this->buildCategory();
        $this->buildPagination();

        return $this->payload;
    }

    /**
     * @throws MissingCategoryException
     */
    protected function buildCategory(): void
    {
        $categoryId = $this->data->category->id ?? throw new MissingCategoryException;

        $this->payload->put('category_id', $categoryId);
    }

    protected function buildPagination(): void
    {
        $this->payload->put('qty', $this->perPage);
        $this->payload->put('offset', ($this->getCurrentPage() - 1) * $this->perPage);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)
            ->map(fn (array $item) => $this->toItemData((int) $item['product_id'], $item))
            ->values();
    }
}
