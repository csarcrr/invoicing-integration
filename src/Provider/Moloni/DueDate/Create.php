<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\DueDate;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldCreateDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\DueDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends DueDate implements ShouldCreateDueDate
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(DueDateData $dueDate)
    {
        $this->data = $dueDate;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::DueDate);
    }

    public function execute(): self
    {
        $response = Http::provider()->post('maturityDates/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = DueDateData::make([
            'id' => $response['maturity_date_id'],
        ] + $this->data->toArray());

        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildName();
        $this->buildDays();
        $this->buildDiscount();

        return $this->payload;
    }

    protected function buildName(): void
    {
        $this->payload->put('name', $this->data->name);
    }

    protected function buildDays(): void
    {
        $this->payload->put('days', $this->data->days);
    }

    protected function buildDiscount(): void
    {
        $this->payload->put('associated_discount', 0);
    }
}
