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

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    protected string $url;

    public function __construct()
    {
        $this->payload = collect();

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $documentSeries */
        $documentSeries = $response->json();

        $this->list = collect($documentSeries);

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    public function getDefaultId(): int
    {
        $series = $this->list->firstWhere('active_by_default', 1) ?? $this->list->first();

        return (int) ($series['document_set_id'] ?? 0);
    }

    protected function buildUrl(): void
    {
        $this->url = 'documentSets/getAll';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('qty', 50);
        $this->payload->put('offset', 0);
    }
}
