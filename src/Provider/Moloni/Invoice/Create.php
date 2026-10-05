<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Invoice;
use CsarCrr\InvoicingIntegration\Traits\HasConfig;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

use function collect;
use function is_null;
use function throw_if;

class Create extends Invoice implements ShouldCreateInvoice
{
    use HasConfig;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);
    }

    /**
     * @throws \Throwable
     */
    public function execute(): self
    {
        $response = Http::provider()->post($this->endpoint(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $this->data = InvoiceData::from([
            'id' => (int) ($data['document_id'] ?? 0),
            'sequence' => '',
            'output' => $this->data->output,
            'items' => $this->data->items,
            'payments' => $this->data->payments,
            'type' => $this->data->type,
        ]);

        $this->fillAdditionalProperties($data);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws \Throwable
     */
    public function getPayload(): Collection
    {
        $this->buildDocument();
        $this->buildClient();
        $this->buildItems();
        $this->buildPayments();
        $this->buildTransport();
        $this->buildDueDate();
        $this->buildNotes();
        $this->buildCreditNoteReason();
        $this->buildRelatedDocument();

        return $this->payload;
    }

    protected function endpoint(): string
    {
        return match ($this->data->type) {
            InvoiceType::Invoice => 'invoices/insert',
            InvoiceType::InvoiceReceipt => 'invoiceReceipts/insert',
            InvoiceType::InvoiceSimple => 'simplifiedInvoices/insert',
            InvoiceType::Receipt => 'receipts/insert',
            InvoiceType::Transport => 'billsOfLading/insert',
            InvoiceType::CreditNote => 'creditNotes/insert',
        };
    }

    protected function buildDocument(): void
    {
        $this->payload->put('date', Carbon::now()->toDateString());
        $this->payload->put('document_set_id', 0);
        $this->payload->put('status', $this->draft ? 0 : 1);
    }

    /**
     * @throws InvoiceRequiresClientVatException|\Throwable
     */
    protected function buildClient(): void
    {
        $client = $this->data->client;

        $this->payload->put('customer_id', 0);

        if (! ($client instanceof ClientData)) {
            return;
        }

        throw_if(Properties::isNotValid($client->vat), InvoiceRequiresClientVatException::class);

        if (is_string($client->id)) {
            $this->payload->put('customer_id', (int) $client->id);
        }
    }

    /**
     * @throws MissingRelatedDocumentException
     * @throws CouldNotGetTaxIdException
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

        $products = $this->data->items->map(fn (ItemData $item): array => $this->product($item));

        if ($products->isEmpty()) {
            return;
        }

        $this->payload->put('products', $products->values()->toArray());
    }

    /**
     * @return array<string, mixed>
     *
     * @throws MissingRelatedDocumentException
     * @throws CouldNotGetTaxIdException
     * @throws \Throwable
     */
    protected function product(ItemData $item): array
    {
        $tax = $this->taxConfig($item);

        $product = [
            'product_id' => (int) $item->id,
            'name' => (string) $item->name,
            'qty' => $item->quantity,
            'price' => round(($item->price ?? 0) / 100 / (1 + ($tax['rate'] ?? 0) / 100), 4),
        ];

        if ($item->note) {
            $product['summary'] = $item->note;
        }

        if ($item->percentageDiscount) {
            $product['discount'] = $item->percentageDiscount;
        }

        if (! is_null($tax)) {
            $product['taxes'] = [[
                'tax_id' => $tax['id'],
                'value' => $tax['rate'],
                'order' => 0,
                'cumulative' => 0,
            ]];
        }

        if ($item->taxExemptionReason) {
            $product['exemption_reason'] = $item->taxExemptionReason->value;
        }

        if ($this->data->type === InvoiceType::CreditNote) {
            throw_if(! $item->relatedDocument, MissingRelatedDocumentException::class);

            $product['related_id'] = $item->relatedDocument->row;
        }

        return $product;
    }

    /**
     * @return array{id: int, rate: float}|null
     *
     * @throws CouldNotGetTaxIdException
     */
    protected function taxConfig(ItemData $item): ?array
    {
        if (! $item->tax || $item->tax === ItemTax::EXEMPT) {
            return null;
        }

        $tax = $this->getConfig()->get('taxes')[$item->tax->value] ?? null;

        throw_if(empty($tax['id']), CouldNotGetTaxIdException::class);

        return [
            'id' => (int) $tax['id'],
            'rate' => (float) ($tax['rate'] ?? 0),
        ];
    }

    /**
     * @throws Exception|\Throwable
     */
    protected function buildPayments(): void
    {
        if (! ($this->data->payments instanceof Collection)) {
            return;
        }

        $payments = $this->data->payments->map(function (PaymentData $payment): array {
            $method = $payment->method;

            throw_if(! $method, Exception::class, 'Payment method not configured.');

            $id = $this->getConfig()->get('payments')[$method->value] ?? null;

            throw_if(! $id, Exception::class, 'Payment method not configured.');

            return [
                'payment_method_id' => (int) $id,
                'date' => Carbon::now()->toDateString(),
                'value' => ($payment->amount ?? 0) / 100,
            ];
        });

        $this->payload->put('payments', $payments->values()->toArray());

        if ($this->data->type === InvoiceType::Receipt) {
            $this->payload->put('net_value', $this->documentTotal());
        }
    }

    /**
     * @throws Exception
     * @throws NeedsDateToSetLoadPointException
     * @throws \Throwable
     */
    protected function buildTransport(): void
    {
        $transport = $this->data->transport;

        if ($transport instanceof Optional) {
            return;
        }

        if ($this->data->client instanceof Optional) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(is_null($transport->origin->dateTime), NeedsDateToSetLoadPointException::class);

        $delivery = collect([
            'delivery_datetime' => $transport->origin->dateTime->toDateTimeString(),
            'delivery_departure_address' => $transport->origin->address,
            'delivery_departure_city' => $transport->origin->city,
            'delivery_departure_zip_code' => $transport->origin->postalCode,
            'delivery_destination_address' => $transport->destination->address,
            'delivery_destination_city' => $transport->destination->city,
            'delivery_destination_zip_code' => $transport->destination->postalCode,
            'vehicle_number_plate' => $transport->vehicleLicensePlate,
        ])->filter(fn (mixed $value) => Properties::isValid($value));

        $this->payload = $this->payload->merge($delivery);
    }

    /**
     * @throws Exception|\Throwable
     */
    protected function buildDueDate(): void
    {
        $this->payload->put('expiration_date', Carbon::now()->toDateString());

        if (! ($this->data->dueDate instanceof Carbon)) {
            return;
        }

        throw_if(
            $this->data->type !== InvoiceType::Invoice,
            Exception::class,
            'Due date can only be set for FT document types.'
        );

        $this->payload->put('expiration_date', $this->data->dueDate->toDateString());
    }

    protected function buildNotes(): void
    {
        if (Properties::isNotValid($this->data->notes)) {
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
        if ($this->data->type === InvoiceType::CreditNote) {
            $this->payload->put('associated_documents', $this->creditedDocuments());

            return;
        }

        $relatedDocument = is_string($this->data->relatedDocument) ? (int) $this->data->relatedDocument : 0;

        if (! $relatedDocument) {
            return;
        }

        $this->payload->put('associated_documents', [[
            'associated_id' => $relatedDocument,
            'value' => $this->documentTotal(),
        ]]);
    }

    /**
     * @return array<int, array{associated_id: int, value: float}>
     */
    protected function creditedDocuments(): array
    {
        /** @var Collection<int, ItemData> $items */
        $items = $this->data->items;

        return $items
            ->groupBy(fn (ItemData $item): int => (int) $item->relatedDocument?->documentId)
            ->map(fn (Collection $items, int $documentId): array => [
                'associated_id' => $documentId,
                'value' => $this->itemsTotal($items),
            ])
            ->values()
            ->toArray();
    }

    protected function documentTotal(): float
    {
        if ($this->data->payments instanceof Collection) {
            return (float) $this->data->payments->sum('amount') / 100;
        }

        if ($this->data->items instanceof Collection) {
            return $this->itemsTotal($this->data->items);
        }

        return 0.0;
    }

    /**
     * @param  Collection<int, ItemData>  $items
     */
    protected function itemsTotal(Collection $items): float
    {
        return (float) $items->sum(
            fn (ItemData $item): float => ($item->price ?? 0) / 100 * $item->quantity * (1 - ($item->percentageDiscount ?? 0) / 100)
        );
    }
}
