<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldDeleteItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;
use function is_int;
use function throw_if;

class Delete extends Item implements ShouldDeleteItem
{
    use HasConfig;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(ItemData $item)
    {
        $this->data = $item;
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);

        $this->buildUrl();
    }

    public function execute(): void
    {
        $response = Http::provider()->delete($this->getUrl());

        Http::handleUnwantedFailures($response);
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
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Item ID is required.');

        $this->url = "products/{$this->data->id}";
    }
}
