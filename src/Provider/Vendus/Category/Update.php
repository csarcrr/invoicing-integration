<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Vendus\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldUpdateCategory;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function is_null;
use function throw_if;

class Update extends Create implements ShouldUpdateCategory
{
    public function execute(): self
    {
        $response = Http::provider()->patch($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = $this->toCategoryData($response->json());

        return $this;
    }

    protected function buildUrl(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Category ID is required.');

        $this->url = "products/categories/{$this->data->id}";
    }

    protected function buildPayload(): void
    {
        if (! is_null($this->data->name)) {
            $this->payload->put('title', $this->data->name);
        }
    }
}
