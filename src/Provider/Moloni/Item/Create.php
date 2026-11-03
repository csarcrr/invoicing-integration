<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
use CsarCrr\InvoicingIntegration\Provider\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends Item implements ShouldCreateItem
{
    use HasConfig;
    use ResolvesTaxConfig;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ItemData $item)
    {
        $this->data = $item;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);
    }

    /**
     * @throws CouldNotGetUnitIdException
     * @throws CouldNotGetTaxIdException
     * @throws MissingCategoryException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('products/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $this->data->id = $data['product_id'];
        $this->data->enabled = true;

        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws CouldNotGetUnitIdException
     * @throws CouldNotGetTaxIdException
     * @throws MissingCategoryException
     */
    public function getPayload(): Collection
    {
        $this->buildName();
        $this->buildReference();
        $this->buildSummary();
        $this->buildEan();
        $this->buildType();
        $this->buildCategory();
        $this->buildUnit();
        $this->buildStock();
        $this->buildTax();
        $this->buildExemptionReason();
        $this->buildPrice();

        return $this->payload;
    }

    protected function buildName(): void
    {
        $this->payload->put('name', $this->data->name);
    }

    protected function buildReference(): void
    {
        if (! $this->data->reference) {
            return;
        }

        $this->payload->put('reference', $this->data->reference);
    }

    protected function buildSummary(): void
    {
        if (! $this->data->description) {
            return;
        }

        $this->payload->put('summary', $this->data->description);
    }

    protected function buildEan(): void
    {
        if (! $this->data->barcode) {
            return;
        }

        $this->payload->put('ean', $this->data->barcode);
    }

    protected function buildType(): void
    {
        $this->payload->put('type', ($this->data->type ?? ItemType::Product)->moloni());
    }

    /**
     * @throws MissingCategoryException
     */
    protected function buildCategory(): void
    {
        $categoryId = $this->data->category->id ?? throw new MissingCategoryException;

        $this->payload->put('category_id', (int) $categoryId);
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

        $this->payload->put('unit_id', (int) $unitId);
    }

    protected function buildStock(): void
    {
        $this->payload->put('has_stock', ($this->data->controlStock ?? true) ? 1 : 0);
        $this->payload->put('stock', 0);
    }

    /**
     * @throws CouldNotGetTaxIdException
     */
    protected function buildTax(): void
    {
        $tax = $this->taxConfig($this->data->tax);

        if (is_null($tax)) {
            return;
        }

        $this->payload->put('taxes', [[
            'tax_id' => $tax['id'],
            'value' => $tax['rate'],
            'order' => 0,
            'cumulative' => 0,
        ]]);
    }

    protected function buildExemptionReason(): void
    {
        if (! $this->data->taxExemptionReason) {
            return;
        }

        $this->payload->put('exemption_reason', $this->data->taxExemptionReason->value);
    }

    /**
     * @throws CouldNotGetTaxIdException
     */
    protected function buildPrice(): void
    {
        if (! $this->data->price) {
            return;
        }

        $rate = $this->taxConfig($this->data->tax)['rate'] ?? 0;

        $this->payload->put('price', round($this->data->price / 100 / (1 + $rate / 100), 4));
    }
}
