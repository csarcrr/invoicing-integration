<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\PaymentMethod;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindPaymentMethod
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, PaymentMethodData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->perPage = 50;
        $this->totalPages(null);
    }

    public function execute(): self
    {
        $request = Http::provider()->post('paymentMethods/getAll', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($request);

        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, PaymentMethodData>
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
        $this->list = collect($results)->map(fn (array $paymentMethod) => PaymentMethodData::make([
            'id' => $paymentMethod['payment_method_id'],
            'name' => $paymentMethod['name'],
            'type' => $this->type($paymentMethod),
        ]))->values();
    }

    /**
     * @param  array<string, mixed>  $paymentMethod
     */
    protected function type(array $paymentMethod): ?PaymentMethod
    {
        return match (true) {
            (bool) ($paymentMethod['is_numerary'] ?? 0) => PaymentMethod::MONEY,
            (bool) ($paymentMethod['is_mb'] ?? 0) => PaymentMethod::MB,
            (bool) ($paymentMethod['is_credit'] ?? 0) => PaymentMethod::CREDIT_CARD,
            default => null,
        };
    }
}
