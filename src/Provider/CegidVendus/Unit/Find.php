<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Unit;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindUnit
{
    use DetectsNoData;

    /** @var Collection<int, UnitData> */
    protected Collection $list;

    public function __construct()
    {
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->get('products/units');

        if ($this->hasNoData($request)) {
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $unit) => UnitData::make([
            'id' => $unit['id'],
            'name' => $unit['title'],
        ]))->values();

        return $this;
    }

    /**
     * @return Collection<int, UnitData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }
}
