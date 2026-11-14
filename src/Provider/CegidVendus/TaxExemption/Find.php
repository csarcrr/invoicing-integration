<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\TaxExemption;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\TaxExemption\ShouldFindTaxExemption;
use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\TaxExemption;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends TaxExemption implements ShouldFindTaxExemption
{
    use DetectsNoData;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, TaxExemptionData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->list = collect();

        $this->buildUrl();
    }

    public function execute(): self
    {
        $response = Http::provider()->get($this->getUrl(), $this->getPayload()->toArray());

        if ($this->hasNoData($response)) {
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $results */
        $results = $response->json();

        $this->list = collect($results)->map(fn (array $exemption) => $this->toTaxExemptionData($exemption))->values();

        return $this;
    }

    /**
     * @return Collection<int, TaxExemptionData>
     */
    public function getList(): Collection
    {
        return $this->list;
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
        $this->url = 'taxes/exemptions';
    }
}
