<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\DocumentType;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType\ShouldFindDocumentType;
use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\DocumentType;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends DocumentType implements ShouldFindDocumentType
{
    use DetectsNoData;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, DocumentTypeData> */
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

        $this->list = collect($results)->map(fn (array $type) => $this->toDocumentTypeData($type))->values();

        return $this;
    }

    /**
     * @return Collection<int, DocumentTypeData>
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
        $this->url = 'documents/types';
    }
}
