<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Provider\Item;
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
        $tax = $data['taxes'][0] ?? null;

        $this->data = ItemData::make([
            'id' => $this->data->id,
            'name' => $data['name'] ?? null,
            'description' => $data['summary'] ?? null,
            'reference' => $data['reference'] ?? null,
            'barcode' => $data['ean'] ?? null,
            'category' => CategoryData::make(['id' => $data['category_id'] ?? null]),
            'controlStock' => (bool) ($data['has_stock'] ?? true),
            'type' => match ($data['type']) {
                1 => ItemType::Product,
                2 => ItemType::Service,
                3 => ItemType::Other,
            },
            'tax' => $tax ? ItemTax::from($tax['tax']['vat_type']) : null,
            'price' => $tax ? (int) round($data['price'] * (1 + $tax['value'] / 100) * 100) : null,
        ]);
    }
}
