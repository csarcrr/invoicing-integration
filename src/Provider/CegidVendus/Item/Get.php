<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Item;
use Illuminate\Support\Facades\Http;

class Get extends Item implements ShouldGetItem
{
    public function __construct(ItemData $itemData)
    {
        $this->data = $itemData;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);
    }

    public function execute(): self
    {
        throw_if(! is_int($this->data->id), \InvalidArgumentException::class, 'Item ID is required.');

        $request = Http::provider()->get("/products/{$this->data->id}");

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->fillProperties($data);
        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillProperties(array $data): void
    {
        $this->data = ItemData::make([
            'id' => $this->data->id,
            'name' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }
}
