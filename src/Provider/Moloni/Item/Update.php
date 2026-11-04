<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Update extends Item implements ShouldUpdateItem
{
    use HasConfig;
    use ResolvesTaxConfig;

    /** @var array<int, string> */
    protected array $carriedFields = [
        'category_id',
        'type',
        'name',
        'summary',
        'reference',
        'ean',
        'price',
        'unit_id',
        'has_stock',
        'visibility_id',
        'minimum_stock',
        'pos_favorite',
        'at_product_category',
        'exemption_reason',
    ];

    /** @var array<string, array<int, string>> */
    protected array $carriedLists = [
        'taxes' => ['tax_id', 'value', 'order', 'cumulative'],
        'suppliers' => ['supplier_id', 'cost_price', 'reference'],
        'properties' => ['property_id', 'value'],
    ];

    /** @var array<int, string> */
    protected array $updatableFields = [
        'name',
        'description',
        'reference',
        'barcode',
        'price',
        'type',
        'tax',
        'taxExemptionReason',
        'category',
        'unit',
        'controlStock',
        'enabled',
    ];

    /** @var array<string, mixed> */
    protected array $current = [];

    public function __construct(ItemData $item)
    {
        $this->data = $item;
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Item);
    }

    /**
     * @throws CouldNotGetUnitIdException
     * @throws CouldNotGetTaxIdException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('products/update', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = $this->updatedItem();
        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws CouldNotGetUnitIdException
     * @throws CouldNotGetTaxIdException
     */
    public function getPayload(): Collection
    {
        throw_if(! is_int($this->data->id), \InvalidArgumentException::class, 'Item ID is required.');

        $unitId = $this->unitId();
        $tax = $this->taxConfig($this->data->tax);
        $current = $this->current = $this->currentProduct();

        return collect(Arr::only($current, $this->carriedFields))
            ->merge($this->carriedLists($current))
            ->merge($this->overrides($current, $unitId, $tax));
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentProduct(): array
    {
        return $this->getProduct(['product_id' => $this->data->id, 'with_invisible' => 1]);
    }

    /**
     * The product read before the update, with the fields the caller set
     * merged over it, since Moloni does not return the updated product.
     */
    protected function updatedItem(): ItemData
    {
        $item = $this->toItemData($this->data->id, $this->current);

        foreach ($this->updatableFields as $field) {
            $item->{$field} = $this->data->{$field} ?? $item->{$field};
        }

        if ($item->tax !== ItemTax::EXEMPT) {
            $item->taxExemptionReason = null;
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $current
     * @return array<string, array<mixed>>
     */
    protected function carriedLists(array $current): array
    {
        return collect($this->carriedLists)
            ->filter(fn (array $keys, string $list): bool => isset($current[$list]))
            ->map(fn (array $keys, string $list): array => array_map(
                fn (array $row): array => Arr::only($row, $keys),
                $current[$list]
            ))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array{id: int, rate: float}|null  $tax
     * @return Collection<string, mixed>
     */
    protected function overrides(array $current, ?int $unitId, ?array $tax): Collection
    {
        return collect([
            'product_id' => $this->data->id,
            'name' => $this->data->name,
            'summary' => $this->data->description,
            'reference' => $this->data->reference,
            'ean' => $this->data->barcode,
            'type' => $this->data->type?->moloni(),
            'category_id' => $this->data->category?->id,
            'unit_id' => $unitId,
            'has_stock' => is_null($this->data->controlStock) ? null : (int) $this->data->controlStock,
            'visibility_id' => is_null($this->data->enabled) ? null : (int) $this->data->enabled,
            'price' => $this->netPrice($current, $tax),
            'taxes' => $this->taxes($tax),
            'exemption_reason' => $this->exemptionReason(),
        ])->reject(fn (mixed $value): bool => is_null($value));
    }

    /**
     * @throws CouldNotGetUnitIdException
     */
    protected function unitId(): ?int
    {
        if (is_null($this->data->unit)) {
            return null;
        }

        return (int) ($this->getConfig()->get('units')[$this->data->unit->getUnitKey()] ?? throw new CouldNotGetUnitIdException);
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array{id: int, rate: float}|null  $tax
     */
    protected function netPrice(array $current, ?array $tax): ?float
    {
        if (is_null($this->data->price) && is_null($this->data->tax)) {
            return null;
        }

        $currentTax = $current['taxes'][0] ?? null;

        $gross = is_null($this->data->price)
            ? (float) ($current['price'] ?? 0) + (float) ($currentTax['value'] ?? 0)
            : $this->data->price / 100;

        $rate = is_null($this->data->tax)
            ? (float) ($currentTax['tax']['value'] ?? 0)
            : ($tax['rate'] ?? 0);

        return round($gross / (1 + $rate / 100), 4);
    }

    /**
     * @param  array{id: int, rate: float}|null  $tax
     * @return array<int, array<string, int|float>>|null
     */
    protected function taxes(?array $tax): ?array
    {
        if (is_null($tax)) {
            return null;
        }

        return [[
            'tax_id' => $tax['id'],
            'value' => $tax['rate'],
            'order' => 0,
            'cumulative' => 0,
        ]];
    }

    protected function exemptionReason(): ?string
    {
        if (! is_null($this->data->taxExemptionReason)) {
            return $this->data->taxExemptionReason->value;
        }

        if (is_null($this->data->tax) || $this->data->tax === ItemTax::EXEMPT) {
            return null;
        }

        return '';
    }
}
