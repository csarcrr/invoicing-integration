<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Providers\SupportedCegidVendusProperties;
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

        $this->item = ItemData::make([
            'id' => $response['id']
        ] + $this->item->toArray());

        $this->fillAdditionalProperties($response->json(), $this->item);

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
        $this->payload->put('title', $this->item->name);
    }

    protected function buildReference(): void
    {
        if (! $this->item->reference) {
            return;
        }

        $this->payload->put('reference', $this->item->reference);
    }

    protected function buildPrice(): void
    {
        if (! $this->item->price) {
            return;
        }

        $this->payload->put('gross_price', $this->item->price / 100);
    }

    protected function buildDescription(): void
    {
        if (! $this->item->description) {
            return;
        }

        $this->payload->put('description', $this->item->description);
    }

    protected function buildType(): void
    {
        if (! $this->item->type) {
            return;
        }

        $this->payload->put('type_id', $this->item->type->vendus());
    }

    protected function buildTax(): void
    {
        if (! $this->item->tax) {
            return;
        }

        $this->payload->put('tax_id', $this->item->tax->vendus());
    }

    protected function buildTaxExemptionReason(): void
    {
        if (! $this->item->taxExemptionReason) {
            return;
        }

        $this->payload->put('tax_exemption', $this->item->taxExemptionReason->value);

        if ($this->item->taxExemptionLaw) {
            $this->payload->put('tax_exemption_law', $this->item->taxExemptionLaw);
        }
    }

    protected function buildBarcode(): void
    {
        if (! $this->item->barcode) {
            return;
        }

        $this->payload->put('barcode', $this->item->barcode);
    }

    protected function buildCategory(): void
    {
        if (! $this->item->category || !$this->item->category->id) {
            return;
        }

        $this->payload->put('category_id', (int) $this->item->category->id);
    }

    protected function buildControlStock(): void
    {
        $this->payload->put('stock_control', $this->item->controlStock ? '1' : '0');
    }

    protected function buildEnabled(): void
    {
        $this->payload->put('status', $this->item->enabled ? 'on' : 'off');
    }

    /**
     * @throws CouldNotGetUnitIdException
     */
    protected function buildUnit(): void
    {
        if (! $this->item->unit) {
            return;
        }

        $unitId = $this->getConfig()->get('units')[strtolower($this->item->unit->value)] ?? throw new CouldNotGetUnitIdException;

        $this->payload->put('unit_id', $unitId);
    }
}
