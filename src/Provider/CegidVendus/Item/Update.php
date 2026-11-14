<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Update extends Item implements ShouldUpdateItem
{
    use HasConfig;

    public function __construct(ItemData $item)
    {
        $this->data = $item;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Item);
    }

    /**
     * @throws CouldNotGetUnitIdException
     */
    public function execute(): self
    {
        throw_if(! is_int($this->data->id), \InvalidArgumentException::class, 'Item ID is required.');

        $response = Http::provider()->patch("/products/{$this->data->id}", $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = $this->toItemData($this->data->id, $response->json());
        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws CouldNotGetUnitIdException
     */
    public function getPayload(): Collection
    {
        return collect([
            'title' => $this->data->name,
            'description' => $this->data->description,
            'reference' => $this->data->reference,
            'barcode' => $this->data->barcode,
            'prices' => is_null($this->data->price) ? null : ['gross' => $this->data->price / 100],
            'type_id' => $this->data->type?->vendus(),
            'tax' => $this->taxPayload(),
            'category_id' => $this->data->category?->id,
            'unit_id' => $this->unitId(),
            'stock' => match ($this->data->controlStock) {
                null => null,
                true => ['control' => '1'],
                false => ['control' => '0'],
            },
            'status' => match ($this->data->enabled) {
                null => null,
                true => 'on',
                false => 'off',
            },
        ])->reject(fn (mixed $value): bool => is_null($value));
    }

    /**
     * @return array<string, string>|null
     */
    protected function taxPayload(): ?array
    {
        $tax = collect([
            'id' => $this->data->tax?->vendus(),
            'exemption' => $this->data->taxExemptionReason,
            'exemption_law' => $this->data->taxExemptionLaw,
        ])->reject(fn (mixed $value): bool => is_null($value))->all();

        return empty($tax) ? null : $tax;
    }

    /**
     * @throws CouldNotGetUnitIdException
     */
    protected function unitId(): mixed
    {
        if (is_null($this->data->unit)) {
            return null;
        }

        return $this->getConfig()->get('units')[$this->data->unit->getUnitKey()] ?? throw new CouldNotGetUnitIdException;
    }
}
