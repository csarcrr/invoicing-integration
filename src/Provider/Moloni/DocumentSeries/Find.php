<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentSeries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find
{
    /** @var Collection<int, array<string, mixed>> */
    protected Collection $list;

    public function execute(): self
    {
        $response = Http::provider()->post('documentSets/getAll', ['qty' => 50, 'offset' => 0]);

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $documentSeries */
        $documentSeries = $response->json();

        $this->list = collect($documentSeries);

        return $this;
    }

    public function getDefaultId(): int
    {
        $series = $this->list->firstWhere('active_by_default', 1) ?? $this->list->first();

        return (int) ($series['document_set_id'] ?? 0);
    }
}
