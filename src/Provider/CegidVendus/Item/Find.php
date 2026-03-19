<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Item;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

/**
 * Handles paginated item search against the Cegid Vendus API.
 */
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
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);
    }

    /**
     * Sends the search request and populates the result list and pagination details.
     */
    public function execute(): self
    {
        $request = Http::provider()->get('/products/', $this->getPayload());

        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request);
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
     */
    public function getPayload(): Collection
    {
        $this->buildPagination();

        $this->buildName();
        $this->buildReference();
        $this->buildBarcode();

        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    private function buildName(): void
    {
        is_string($this->data->name) && $this->payload->put('title', $this->data->name);
    }

    private function buildReference(): void
    {
        ! is_null($this->data->reference) && $this->payload->put('reference', $this->data->reference);
    }

    private function buildBarcode(): void
    {
        is_string($this->data->barcode) && $this->payload->put('barcode', $this->data->barcode);
    }

    protected function updatePaginationDetails(Response $results): void
    {
        $this->totalPages((int) $results->header('X-Paginator-Pages') ?: 1);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(function (array $item) {
            $data = [];
            ! empty($item['title']) && $data['name'] = $item['title'];
            ! empty($item['reference']) && $data['reference'] = $item['reference'];
            ! empty($item['barcode']) && $data['barcode'] = $item['barcode'];
            ! empty($item['id']) && $data['id'] = $item['id'];

            return ItemData::from($data);
        })->values();
    }
}
