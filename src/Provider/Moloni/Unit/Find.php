<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Unit;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindUnit
{
    /** @var Collection<int, UnitData> */
    protected Collection $list;

    public function __construct()
    {
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->post('measurementUnits/getAll');

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $unit) => UnitData::make([
            'id' => $unit['unit_id'],
            'name' => $unit['name'],
            'shortName' => $unit['short_name'] ?? null,
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
