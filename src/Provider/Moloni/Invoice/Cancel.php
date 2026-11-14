<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

class Cancel implements ShouldCancelInvoice
{
    public function __construct(protected InvoiceData $invoice) {}

    public function execute(): void
    {
        throw_if(! is_int($this->invoice->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $payload = ['document_id' => $this->invoice->id];

        if (Properties::isValid($this->invoice->notes)) {
            $payload['observation'] = $this->invoice->notes;
        }

        $response = Http::provider()->post('documents/documentCancel', $payload);

        Http::handleUnwantedFailures($response);

        throw_if(
            (int) $response->json('valid', 0) !== 1,
            RequestFailedException::class,
            (string) $response->json('message', 'Invoice could not be cancelled.')
        );
    }
}
