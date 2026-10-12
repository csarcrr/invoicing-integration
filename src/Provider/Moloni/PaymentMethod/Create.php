<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\PaymentMethod;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldCreatePaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\PaymentMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Create extends PaymentMethod implements ShouldCreatePaymentMethod
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(PaymentMethodData $paymentMethod)
    {
        $this->data = $paymentMethod;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::PaymentMethod);
    }

    public function execute(): self
    {
        $response = Http::provider()->post('paymentMethods/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = PaymentMethodData::make([
            'id' => $response['payment_method_id'],
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
        $this->buildType();

        return $this->payload;
    }

    protected function buildName(): void
    {
        $this->payload->put('name', $this->data->name);
    }

    protected function buildType(): void
    {
        $type = $this->data->type;

        $this->payload->put('is_numerary', (int) ($type === PaymentMethodEnum::MONEY));
        $this->payload->put('is_mb', (int) ($type === PaymentMethodEnum::MB));
        $this->payload->put('is_credit', (int) ($type === PaymentMethodEnum::CREDIT_CARD));
    }
}
