<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Invoice;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

use function collect;

/**
 * Handles invoice creation against the Cegid Vendus API.
 */
class Create extends Invoice implements ShouldCreateInvoice
{
    use HasConfig;

    /**
     * @var Collection<string, mixed>
     */
    protected Collection $payload;

    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;

        $this->payload = collect([
            'type' => $this->data->type->value,
        ]);

        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Invoice);
    }

    /**
     * @throws \Throwable
     * @throws \Exception
     * @throws MissingRelatedDocumentException
     * @throws NeedsDateToSetLoadPointException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('documents', $this->getPayload());

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $output = OutputData::make([
            'format' => $this->data->output->format->value,
            'content' => $data['output'] ?? null,
            'fileName' => $data['number'] ?? null,
        ]);

        $this->data = InvoiceData::from([
            'id' => (int) ($data['id'] ?? 0),
            'sequence' => (string) ($data['number'] ?? ''),
            'total' => (int) ((float) ($data['amount_gross'] ?? 0) * 100),
            'totalNet' => (int) ((float) ($data['amount_net'] ?? 0) * 100),
            'atcudHash' => $data['atcud'] ?? null,
            'output' => $output,
            'items' => $this->data->items,
            'payments' => $this->data->payments,
            'type' => $this->data->type,
        ]);

        $this->fillAdditionalProperties($data);

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
        $this->payload->put('type', $this->data->type->value);
    }

    /**
     * @throws Exception|\Throwable
     */
    protected function buildDueDate(): void
    {
        if (! ($this->data->dueDate instanceof Carbon)) {
            return;
        }

        throw_if(
            $this->data->type !== InvoiceType::Invoice,
            Exception::class,
            'Due date can only be set for FT document types.'
        );

        $this->payload->put('due_date', $this->data->dueDate->toDateString());
    }

    protected function buildOutput(): void
    {
        $this->payload->put('output', $this->data->output->format->vendus());
    }

    /**
     * @throws Exception
     * @throws NeedsDateToSetLoadPointException
     */
    protected function buildTransport(): void
    {
        if ($this->data->transport instanceof Optional) {
            return;
        }

        if ($this->data->client instanceof Optional) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(is_null($this->data->transport->origin->dateTime), NeedsDateToSetLoadPointException::class);

        $data = [];

        $data['loadpoint'] = [
            'date' => $this->data->transport->origin->dateTime->toDateString(),
            'time' => $this->data->transport->origin->dateTime->format('H:i'),
            'address' => $this->data->transport->origin->address,
            'postalcode' => $this->data->transport->origin->postalCode,
            'city' => $this->data->transport->origin->city,
            'country' => $this->data->transport->origin->country,
        ];

        $landpointData = [
            'address' => $this->data->transport->destination->address,
            'postalcode' => $this->data->transport->destination->postalCode,
            'city' => $this->data->transport->destination->city,
            'country' => $this->data->transport->destination->country,
        ];

        if ($this->data->transport->destination->dateTime) {
            $landpointData['date'] = $this->data->transport->destination->dateTime->toDateString();
            $landpointData['time'] = $this->data->transport->destination->dateTime->format('H:i');
        }

        $data['landpoint'] = $landpointData;

        if ($this->data->transport->vehicleLicensePlate) {
            $data['vehicle_id'] = $this->data->transport->vehicleLicensePlate;
        }

        $this->payload->put('movement_of_goods', $data);
    }

    protected function buildNotes(): void
    {
        if (! $this->data->notes) {
            return;
        }

        $this->payload->put('notes', $this->data->notes);
    }

    /**
     * @throws CreditNoteReasonIsMissingException
     * @throws \Throwable
     */
    protected function buildCreditNoteReason(): void
    {
        if ($this->data->type !== InvoiceType::CreditNote) {
            return;
        }

        throw_if(
            is_null($this->data->creditNoteReason),
            CreditNoteReasonIsMissingException::class
        );

        $this->payload->put('notes', $this->data->creditNoteReason);
    }

    protected function buildRelatedDocument(): void
    {
        $relatedDocument = is_string($this->data->relatedDocument) ? (int) $this->data->relatedDocument : null;

        if ($this->data->type === InvoiceType::CreditNote || ! $relatedDocument) {
            return;
        }

        $this->payload->put('related_document_id', $this->data->relatedDocument);
    }

    /**
     * @throws Exception
     */
    protected function buildPayments(): void
    {
        if (($this->data->payments instanceof Collection) === false) {
            return;
        }

        $payments = $this->data->payments->map(function (PaymentData $payment) {
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
     * @throws \Throwable
     */
    protected function buildItems(): void
    {
        if ($this->data->type === InvoiceType::Receipt) {
            return;
        }

        throw_if(
            ! ($this->data->items instanceof Collection),
            Exception::class, 'Invoice items not set.'
        );

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $this->data->items->map(function (ItemData $item): array {
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

            if ($this->data->type === InvoiceType::CreditNote) {
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
        $client = $this->data->client;

        if (! ($client instanceof ClientData)) {
            return;
        }

        throw_if(Properties::isNotValid($client->vat), InvoiceRequiresClientVatException::class);

        $data = $client->toArray();

        $data['irs_retention'] = $this->booleanFlag($client->irsRetention);
        $data['email_notification'] = $this->booleanFlag($client->emailNotification);

        $data['fiscal_id'] = $client->vat;

        $postalCode = $client->postalCode;

        if (Properties::isValid($postalCode)) {
            $data['postalcode'] = $postalCode;
        }

        unset($data['vat'], $data['postal_code']);

        $this->payload->put('client', $data);
    }

    private function booleanFlag(Optional|bool|null $value): string
    {
        if (! is_bool($value)) {
            return 'no';
        }

        return $value ? 'yes' : 'no';
    }
}
