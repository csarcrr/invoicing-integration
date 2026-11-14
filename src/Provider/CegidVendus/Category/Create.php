<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldCreateCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends Category implements ShouldCreateCategory
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(CategoryData $category)
    {
        $this->data = $category;
        $this->payload = collect();

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = $this->toCategoryData($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    public function getCategory(): CategoryData
    {
        return $this->data;
    }

    protected function buildUrl(): void
    {
        $this->url = 'products/categories';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('title', $this->data->name);
    }
}
