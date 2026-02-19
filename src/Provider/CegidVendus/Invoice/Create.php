<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusInvoice;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasClient;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasCreditNoteReason;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasDueDate;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasItems;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasNotes;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasOutputFormat;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasPayments;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasRelatedDocument;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasTransport;
use CsarCrr\InvoicingIntegration\Traits\Invoice\HasType;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;
use function collect;

class Create extends CegidVendusInvoice implements ShouldCreateInvoice, ShouldHaveConfig, ShouldHavePayload
{
    use HasClient;
    use HasConfig;
    use HasCreditNoteReason;
    use HasDueDate;
    use HasItems;
    use HasNotes;
    use HasOutputFormat;
    use HasPayments;
    use HasRelatedDocument;
    use HasTransport;
    use HasType;

    /**
     * @var Collection<string, mixed>
     */
    protected Collection $payload;

    /**
     * @param InvoiceData $invoice
     */
    public function __construct(protected InvoiceData $invoice)
    {
        $this->payload = collect([
            'type' => $this->getType()->value,
        ]);

        $this->items = collect();
        $this->payments = collect();
    }

    /**
     * @throws \Throwable
     * @throws MissingRelatedDocumentException
     * @throws NeedsDateToSetLoadPointException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('documents', $this->getPayload());

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $output = isset($data['output'])
            ? new Output(
                format: $this->getOutputFormat(),
                content: $data['output'],
                fileName: $data['number'] ?? ''
            )
            : null;

        $this->invoice = InvoiceData::from([
            'id' => (int) ($data['id'] ?? 0),
            'sequence' => (string) ($data['number'] ?? ''),
            'total' => (int) ((float) ($data['amount_gross'] ?? 0) * 100),
            'totalNet' => (int) ((float) ($data['amount_net'] ?? 0) * 100),
            'atcudHash' => $data['atcud'] ?? null,
            'output' => $output,
            'items' => collect($this->items),
            'payments' => collect($this->payments),
            'type' => $this->getType(),
        ]);

        $this->fillAdditionalProperties($data, $this->invoice);

        return $this;
    }

    /**
     * Get the payload to send to the provider
     *
     * @return Collection<string, mixed>
     *
     * @throws MissingRelatedDocumentException
     * @throws NeedsDateToSetLoadPointException
     * @throws \Exception
     * @throws \Throwable
     */
    public function getPayload(): Collection
    {
        $this->buildType();
        $this->buildClient();
        $this->buildItems();
        $this->buildPayments();
        $this->buildTransport();
        $this->buildOutput();
        $this->buildDueDate();
        $this->buildNotes();
        $this->buildCreditNoteReason();
        $this->buildRelatedDocument();

        return $this->payload;
    }

    protected function buildType(): void
    {
        $this->payload->put('type', $this->invoice->type->value);
    }

    /**
     * @throws Exception|\Throwable
     */
    protected function buildDueDate(): void
    {
        if (!($this->invoice->dueDate instanceof Carbon)) {
            return;
        }

        throw_if(
            $this->invoice->type !== InvoiceType::Invoice,
            Exception::class,
            'Due date can only be set for FT document types.'
        );

        $this->payload->put('due_date', $this->invoice->dueDate->toDateString());
    }

    protected function buildOutput(): void
    {
        $this->payload->put('output', $this->getOutputFormat()->vendus());
    }

    /**
     * @throws Exception
     * @throws NeedsDateToSetLoadPointException
     */
    protected function buildTransport(): void
    {
        if ($this->invoice->transport instanceof Optional) {
            return;
        }

        if ($this->invoice->client  instanceof Optional) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(is_null($this->invoice->transport->origin->dateTime), NeedsDateToSetLoadPointException::class);

        $data = [];

        $data['loadpoint'] = [
            'date' => $this->invoice->transport->origin->dateTime->toDateString(),
            'time' => $this->invoice->transport->origin->dateTime->format('H:i'),
            'address' => $this->invoice->transport->origin->address,
            'postalcode' => $this->invoice->transport->origin->postalCode,
            'city' => $this->invoice->transport->origin->city,
            'country' => $this->invoice->transport->origin->country,
        ];

        $landpointData = [
            'address' => $this->invoice->transport->destination->address,
            'postalcode' => $this->invoice->transport->destination->postalCode,
            'city' => $this->invoice->transport->destination->city,
            'country' => $this->invoice->transport->destination->country,
        ];

        if ($this->invoice->transport->destination->dateTime) {
            $landpointData['date'] = $this->invoice->transport->destination->dateTime->toDateString();
            $landpointData['time'] = $this->invoice->transport->destination->dateTime->format('H:i');
        }

        $data['landpoint'] = $landpointData;

        if ($this->invoice->transport->vehicleLicensePlate) {
            $data['vehicle_id'] = $this->invoice->transport->vehicleLicensePlate;
        }

        $this->payload->put('movement_of_goods', $data);
    }

    protected function buildNotes(): void
    {
        if (! $this->getNotes()) {
            return;
        }

        $this->payload->put('notes', $this->getNotes());
    }

    /**
     * @throws CreditNoteReasonIsMissingException
     * @throws \Throwable
     */
    protected function buildCreditNoteReason(): void
    {
        if ($this->getType() !== InvoiceType::CreditNote) {
            return;
        }

        throw_if(
            is_null($this->getCreditNoteReason()),
            CreditNoteReasonIsMissingException::class
        );

        $this->payload->put('notes', $this->getCreditNoteReason());
    }

    protected function buildRelatedDocument(): void
    {
        if ($this->getType() === InvoiceType::CreditNote) {
            return;
        }

        if (! $this->getRelatedDocument()) {
            return;
        }

        $this->payload->put('related_document_id', (int) $this->getRelatedDocument());
    }

    /**
     * @throws Exception
     */
    protected function buildPayments(): void
    {
        if (($this->invoice->payments instanceof Collection) === false) {
            return;
        }

        $payments = $this->invoice->payments->map(function (PaymentData $payment) {
            $method = $payment->method;

            throw_if(! $method, Exception::class, 'Payment method not configured.');

            $id = $this->getConfig()->get('payments')[$method->value] ?? null;

            throw_if(! $id, Exception::class, 'Payment method not configured.');

            return [
                'amount' => (float) (($payment->amount ?? 0) / 100),
                'id' => $id,
            ];
        });

        $this->payload->put('payments', $payments);
    }

    /**
     * @throws MissingRelatedDocumentException
     */
    protected function buildItems(): void
    {
        if ($this->getType() === InvoiceType::Receipt) {
            return;
        }

        throw_if($this->invoice->items instanceof Optional, Exception::class, 'Invoice items not set.');

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $this->invoice->items->map(function (ItemData $item): array {
            $data = [];

            if ($item->reference) {
                $data['reference'] = $item->reference;
            }

            if ($item->price) {
                $data['gross_price'] = $item->price / 100;
            }

            if ($item->quantity) {
                $data['qty'] = $item->quantity;
            }

            if ($item->note) {
                $data['note'] = $item->note;
            }

            if ($item->type) {
                $data['type_id'] = $item->type->vendus();
            }

            if ($item->percentageDiscount) {
                $data['discount_percent'] = $item->percentageDiscount;
            }

            if ($item->amountDiscount) {
                $data['discount_amount'] = $item->amountDiscount / 100;
            }

            if ($item->tax) {
                $data['tax_id'] = $item->tax->vendus();
            }

            if ($item->taxExemptionReason) {
                $data['tax_exemption'] = $item->taxExemptionReason->value;

                if ($item->taxExemptionLaw) {
                    $data['tax_exemption_law'] = $item->taxExemptionLaw;
                }
            }

            if ($this->getType() === InvoiceType::CreditNote) {
                throw_if(
                    ! $item->relatedDocument,
                    MissingRelatedDocumentException::class
                );

                $data['reference_document'] = [
                    'document_number' => $item->relatedDocument->documentId,
                    'document_row' => $item->relatedDocument->row,
                ];
            }

            return $data;
        });

        if ($items->isEmpty()) {
            return;
        }

        $this->payload->put('items', $items);
    }

    /**
     * @throws InvoiceRequiresClientVatException|\Throwable
     */
    protected function buildClient(): void
    {
        $client = $this->invoice->client;

        if (empty($client) || $client instanceof Optional) {
            return;
        }

        throw_if(
            empty($client->vat),
            InvoiceRequiresClientVatException::class
        );

        $data = $client->toArray();

        $data['irs_retention'] = $client->irsRetention ? 'yes' : 'no';
        $data['email_notification'] = $client->emailNotification ? 'yes' : 'no';
        ! empty($client->vat) && $data['fiscal_id'] = $client->vat;
        ! empty($client->postalCode) && $data['postalcode'] = $client->postalCode;

        unset($data['vat'], $data['postal_code']);

        $this->payload->put('client', $data);
    }
}
