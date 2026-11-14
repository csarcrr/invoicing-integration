<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Tax;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldCreateTax;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Tax;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends Tax implements ShouldCreateTax
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(TaxData $tax)
    {
        $this->data = $tax;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Tax);
    }

    public function execute(): self
    {
        $response = Http::provider()->post('taxes/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = TaxData::make([
            'id' => $response['tax_id'],
        ] + $this->data->toArray());

        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->payload = collect([
            'name' => $this->data->name,
            'value' => $this->data->rate,
            'type' => 1,
            'saft_type' => 1,
            'vat_type' => $this->data->type?->value,
            'stamp_tax' => '',
            'exemption_reason' => $this->data->exemptionReason ?? '',
            'fiscal_zone' => $this->data->fiscalZone,
            'active_by_default' => 0,
        ])->reject(fn (mixed $value) => is_null($value));

        return $this->payload;
    }
}
