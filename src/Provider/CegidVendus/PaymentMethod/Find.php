<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\PaymentMethod;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindPaymentMethod
{
    use DetectsNoData;
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, PaymentMethodData> */
    protected Collection $list;

    public function __construct()
    {
        $this->payload = collect();
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->get('documents/paymentmethods');

        $this->totalPages(1);

        if ($this->hasNoData($request)) {
            $this->list = collect();

            return $this;
        }

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $paymentMethod) => PaymentMethodData::make([
            'id' => $paymentMethod['id'],
            'name' => $paymentMethod['title'],
            'type' => $this->type((string) ($paymentMethod['type'] ?? '')),
        ]))->values();

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
        return $this->payload;
    }

    protected function type(string $type): ?PaymentMethod
    {
        return match ($type) {
            'NU' => PaymentMethod::MONEY,
            'CD' => PaymentMethod::MB,
            'CC' => PaymentMethod::CREDIT_CARD,
            'DNP' => PaymentMethod::CURRENT_ACCOUNT,
            'TB' => PaymentMethod::MONEY_TRANSFER,
            default => null,
        };
    }
}
