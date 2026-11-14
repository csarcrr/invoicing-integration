<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Tax;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldFindTax;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindTax
{
    /** @var Collection<int, TaxData> */
    protected Collection $list;

    public function __construct()
    {
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->post('taxes/getAll');

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $tax) => TaxData::make([
            'id' => $tax['tax_id'],
            'name' => $tax['name'],
            'rate' => (float) $tax['value'],
            'type' => ItemTax::tryFrom((string) ($tax['vat_type'] ?? '')),
            'fiscalZone' => $tax['fiscal_zone'] ?? null,
            'exemptionReason' => ((string) ($tax['exemption_reason'] ?? '')) ?: null,
        ]))->values();

        return $this;
    }

    /**
     * @return Collection<int, TaxData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }
}
