<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Vendus\DueDate;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Provider\Vendus\DueDate;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends DueDate implements ShouldFindDueDate
{
    use DetectsNoData;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, DueDateData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->list = collect();

        $this->buildUrl();
    }

    public function execute(): self
    {
        $request = Http::provider()->get($this->getUrl(), $this->getPayload()->toArray());

        $this->totalPages(1);

        if ($this->hasNoData($request)) {
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($request);

        /** @var array<string|int, string> $results */
        $results = $request->json('results', []);

        $this->list = collect($results)
            ->map(fn (string $name, string|int $key) => $this->toDueDateData($key, $name))
            ->values();

        return $this;
    }

    /**
     * @return Collection<int, DueDateData>
     */
    public function get(): Collection
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

    protected function toDueDateData(string|int $key, string $name): DueDateData
    {
        return DueDateData::make([
            'name' => $name,
            'days' => $key === 'now' ? 0 : (int) $key,
        ])->additional(['key' => (string) $key]);
    }

    protected function buildUrl(): void
    {
        $this->url = 'documents/duedates';
    }
}
