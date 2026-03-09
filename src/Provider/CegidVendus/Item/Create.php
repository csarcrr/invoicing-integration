<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Provider\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

/**
 * Handles item creation against the Cegid Vendus API.
 */
class Create extends Item implements ShouldCreateItem
{
    use HasConfig;

    /**
     * @var Collection<string, mixed>
     */
    protected Collection $payload;

    public function __construct(protected ?ItemData $item)
    {
        $this->data = $item;
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);
    }

    /**
     * Sends the create request to the Cegid Vendus products endpoint.
     *
     * @throws CouldNotGetUnitIdException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('products', $this->getPayload());

        Http::handleUnwantedFailures($response);

        $this->data = ItemData::make([
            'id' => $response['id'],
        ] + $this->data->toArray());

        $this->fillAdditionalProperties($response->json(), $this->data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws CouldNotGetUnitIdException
     */
    public function getPayload(): Collection
    {
        $this->buildTitle();
        $this->buildReference();
        $this->buildPrice();
        $this->buildDescription();
        $this->buildType();
        $this->buildTax();
        $this->buildTaxExemptionReason();
        $this->buildBarcode();
        $this->buildCategory();
        $this->buildControlStock();
        $this->buildEnabled();
        $this->buildUnit();

        return $this->payload;
    }

    protected function buildTitle(): void
    {
        $this->payload->put('title', $this->data->name);
    }

    protected function buildReference(): void
    {
        if (! $this->data->reference) {
            return;
        }

        $this->payload->put('reference', $this->data->reference);
    }

    protected function buildPrice(): void
    {
        if (! $this->data->price) {
            return;
        }

        $this->payload->put('gross_price', $this->data->price / 100);
    }

    protected function buildDescription(): void
    {
        if (! $this->data->description) {
            return;
        }

        $this->payload->put('description', $this->data->description);
    }

    protected function buildType(): void
    {
        if (! $this->data->type) {
            return;
        }

        $this->payload->put('type_id', $this->data->type->vendus());
    }

    protected function buildTax(): void
    {
        if (! $this->data->tax) {
            return;
        }

        $this->payload->put('tax_id', $this->data->tax->vendus());
    }

    protected function buildTaxExemptionReason(): void
    {
        if (! $this->data->taxExemptionReason) {
            return;
        }

        $this->payload->put('tax_exemption', $this->data->taxExemptionReason->value);

        if ($this->data->taxExemptionLaw) {
            $this->payload->put('tax_exemption_law', $this->data->taxExemptionLaw);
        }
    }

    protected function buildBarcode(): void
    {
        if (! $this->data->barcode) {
            return;
        }

        $this->payload->put('barcode', $this->data->barcode);
    }

    protected function buildCategory(): void
    {
        if (! $this->data->category || ! $this->data->category->id) {
            return;
        }

        $this->payload->put('category_id', (int) $this->data->category->id);
    }

    protected function buildControlStock(): void
    {
        $this->payload->put('stock_control', $this->data->controlStock ? '1' : '0');
    }

    protected function buildEnabled(): void
    {
        $this->payload->put('status', $this->data->enabled ? 'on' : 'off');
    }

    /**
     * @throws CouldNotGetUnitIdException
     */
    protected function buildUnit(): void
    {
        if (! $this->data->unit) {
            return;
        }

        $unitId = $this->getConfig()->get('units')[$this->data->unit->getUnitKey()] ?? throw new CouldNotGetUnitIdException;

        $this->payload->put('unit_id', $unitId);
    }
}
