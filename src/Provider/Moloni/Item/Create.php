<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Create extends Item implements ShouldCreateItem
{
    use HasConfig;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ItemData $item)
    {
        $this->data = $item;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);
    }

    public function execute(): self
    {
        $response = Http::provider()->post('products', $this->getPayload());

        Http::handleUnwantedFailures($response);

        $this->data = ItemData::make([
            'id' => $response['id'],
        ] + $this->data->toArray());

        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /** @return Collection<string, mixed> */
    public function getPayload(): Collection
    {
        return $this->payload;
    }
}
