<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusItem;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends CegidVendusItem implements ShouldCreateItem, ShouldExecute, ShouldHavePayload
{
    use HasConfig;

    /**
     * @var Collection<string, mixed>
     */
    protected Collection $payload;

    public function __construct(protected ?ItemData $item)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $response = Http::provider()->post('products', $this->getPayload());

        Http::handleUnwantedFailures($response);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildTitle();
        $this->buildReference();
        $this->buildQuantity();
        $this->buildPrice();
        $this->buildDescription();
        $this->buildType();
        $this->buildTax();
        $this->buildTaxExemptionReason();

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

    protected function buildQuantity(): void
    {
        if (! $this->item->quantity) {
            return;
        }

        $this->payload->put('qty', $this->item->quantity);
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
}
