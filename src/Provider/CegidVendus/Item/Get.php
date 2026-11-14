<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Get extends Item implements ShouldGetItem
{
    use HasConfig;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(ItemData $itemData)
    {
        $this->data = $itemData;
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);

        $this->buildUrl();
    }

    public function execute(): self
    {
        $request = Http::provider()->get($this->getUrl());

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
        return $this->payload;
    }

    protected function buildUrl(): void
    {
        throw_if(! is_int($this->data->id), \InvalidArgumentException::class, 'Item ID is required.');

        $this->url = "/products/{$this->data->id}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillProperties(array $data): void
    {
        $this->data = $this->toItemData($this->data->id, $data);
    }
}
