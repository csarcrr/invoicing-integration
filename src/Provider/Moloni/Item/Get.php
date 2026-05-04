<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Provider\Item;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

/**
 * Handles paginated item search against the Cegid Vendus API.
 */
class Get extends Item implements ShouldGetItem
{
    public function __construct(protected ?ItemData $item = null)
    {
        $this->data = $item;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);

        $this->payload = collect([
            'company_id' => 386389
        ]);
    }

    /**
     * Sends the search request and populates the result list and pagination details.
     */
    public function execute(): Get
    {
        $request = Http::provider()->asForm()->post('/products/getOne/', $this->getPayload());

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
        throw_if(!$this->item->id, \Exception::class, 'Moloni only is able to get an item via their ID.');

        $this->item->id && $this->payload->put('product_id', $this->item->id);
    }

    protected function fillProperties(array $data): void
    {
        $type = match($data['type']) {
            1 => ItemType::Product,
        };

        $this->data = ItemData::make([
            'id' => $this->data->id,
            'reference' => $data['reference'],
            'notes' => $data['notes'],
            'type' => $type,
            'name' => $data['name'],
            'price' => ($data['price'] + $data['taxes'][0]['value']) * 100,
            'unit_id' => $data['unit_id'],
            'has_stock' => $data['has_stock'],
            'stock' => $data['stock'],
            'tax' => ItemTax::from($data['taxes'][0]['tax']['vat_type'])
        ]);
    }
}
