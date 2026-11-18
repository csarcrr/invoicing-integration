<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Category;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldUpdateCategory;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function is_null;
use function throw_if;

class Update extends Create implements ShouldUpdateCategory
{
    public function execute(): static
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Category could not be updated.')
        );

        return $this;
    }

    protected function buildUrl(): void
    {
        $this->url = 'productCategories/update';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Category ID is required.');

        $this->payload->put('category_id', $this->data->id);

        if (! is_null($this->data->name)) {
            $this->payload->put('name', $this->data->name);
        }

        if (! is_null($this->data->parentId)) {
            $this->payload->put('parent_id', $this->data->parentId);
        }
    }
}
