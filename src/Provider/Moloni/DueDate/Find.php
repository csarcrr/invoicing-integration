<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\DueDate;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindDueDate
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, DueDateData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->totalPages(null);
    }

    public function execute(): self
    {
        $request = Http::provider()->post('maturityDates/getAll', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, DueDateData>
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
        $this->buildPagination();

        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('qty', $this->perPage);
        $this->payload->put('offset', ($this->getCurrentPage() - 1) * $this->perPage);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(fn (array $dueDate) => DueDateData::make([
            'id' => $dueDate['maturity_date_id'],
            'name' => $dueDate['name'],
            'days' => (int) $dueDate['days'],
        ]))->values();
    }
}
