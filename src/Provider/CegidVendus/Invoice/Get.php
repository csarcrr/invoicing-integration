<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Invoice;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

class Get extends Invoice implements ShouldGetInvoice
{
    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Invoice);
    }

    public function execute(): self
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $response = Http::provider()->get('documents/'.$this->data->id);

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $this->data = InvoiceData::from([
            'id' => (int) $data['id'],
            'sequence' => (string) $data['number'],
            'total' => $this->toCents((float) $data['amount_gross']),
            'totalNet' => $this->toCents((float) $data['amount_net']),
            'atcudHash' => $data['atcud'] ?? null,
            'type' => InvoiceType::tryFrom($data['type'] ?? '') ?? InvoiceType::Invoice,
        ]);

        $this->fillAdditionalProperties($data);

        return $this;
    }
}
