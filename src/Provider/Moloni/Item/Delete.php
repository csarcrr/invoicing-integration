<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldDeleteItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item;
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
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): void
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Item could not be deleted.')
        );
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
        $this->url = 'products/delete';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Item ID is required.');

        $this->payload->put('product_id', $this->data->id);
    }
}
