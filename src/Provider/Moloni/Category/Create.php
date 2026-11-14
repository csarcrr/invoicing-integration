<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldCreateCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;
use function throw_if;

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

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Category could not be created.')
        );

        $this->data = CategoryData::make([
            'id' => (int) $response->json('category_id'),
            'name' => $this->data->name,
            'parentId' => $this->data->parentId,
        ]);

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
        $this->url = 'productCategories/insert';
    }

    protected function buildPayload(): void
    {
        $this->payload->put('name', $this->data->name);
        $this->payload->put('parent_id', $this->data->parentId ?? 0);
    }
}
