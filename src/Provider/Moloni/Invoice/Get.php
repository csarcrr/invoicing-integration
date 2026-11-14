<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;
use CsarCrr\InvoicingIntegration\Traits\DetectsNoData;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

class Get extends Invoice implements ShouldGetInvoice
{
    use DetectsNoData;

    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);
    }

    public function execute(): self
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $response = Http::provider()->post('documents/getOne', ['document_id' => $this->data->id]);

        Http::handleUnwantedFailures($response);

        throw_if($this->hasNoData($response), RequestFailedException::class, 'Invoice not found.');

        $data = $response->json();

        $total = (float) ($data['net_value'] ?? 0);

        $this->data = InvoiceData::from([
            'id' => $this->data->id,
            'sequence' => $this->sequence($data),
            'total' => $this->toCents($total),
            'totalNet' => $this->toCents($total - (float) ($data['taxes_value'] ?? 0)),
            'type' => InvoiceType::tryFrom($data['document_type']['saft_code'] ?? '') ?? InvoiceType::Invoice,
        ]);

        $this->fillAdditionalProperties($data);

        return $this;
    }
}
