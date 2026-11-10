<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\PaymentReturn;

use Illuminate\Support\Facades\Http;

class Create
{
    protected int $id = 0;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(protected array $payload) {}

    public function execute(): self
    {
        $response = Http::provider()->post('paymentReturns/insert', $this->payload);

        Http::handleUnwantedFailures($response);

        $this->id = (int) $response->json('document_id');

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
