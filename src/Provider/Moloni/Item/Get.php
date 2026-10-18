<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Get extends Item implements ShouldGetItem
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(ItemData $item)
    {
        $this->data = $item;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);
    }

    public function execute(): self
    {
        $request = Http::provider()->post('products/getOne', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->fillProperties($data);
        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildId();

        return $this->payload;
    }

    protected function buildId(): void
    {
        throw_if(! is_int($this->data->id), \InvalidArgumentException::class, 'Item ID is required.');

        $this->payload->put('product_id', $this->data->id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillProperties(array $data): void
    {
        $this->data = $this->toItemData($this->data->id, $data);
    }
}
