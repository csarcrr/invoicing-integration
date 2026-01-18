<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHaveConfig;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\MissingRelatedDocumentException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonIsMissingException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\IntegrationProvider\Request;
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
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Create implements CreateInvoice, ShouldHaveConfig, ShouldHavePayload
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
     * @var array<int, InvoiceType>
     */
    protected array $invoiceTypesThatRequirePayments = [
        InvoiceType::Receipt,
        InvoiceType::InvoiceReceipt,
        InvoiceType::InvoiceSimple,
        InvoiceType::CreditNote,
    ];

    /**
     * @param  array<string, mixed>|Collection<string, mixed>  $config
     */
    public function __construct(array|Collection $config)
    {
        $this->config($config);
        $this->payload = collect([
            'type' => $this->getType()->value,
        ]);
        $this->items = collect();
        $this->payments = collect();
    }

    /**
     * Request an invoice creation
     */
    public function execute(): Invoice
    {
        /** @phpstan-ignore-next-line */
        $response = Http::provider()->post('documents', $this->getPayload());

        /** @phpstan-ignore-next-line */
        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $invoice = new Invoice;

        if (isset($data['id'])) {
            $invoice->id($data['id']);
        }

        if (isset($data['number'])) {
            $invoice->sequence($data['number']);
        }

        if (isset($data['amount_gross'])) {
            $invoice->total((int) ((float) $data['amount_gross'] * 100));
        }

        if (isset($data['amount_net'])) {
            $invoice->totalNet((int) ((float) $data['amount_net'] * 100));
        }

        if (isset($data['atcud'])) {
            $invoice->atcudHash($data['atcud']);
        }

        if (isset($data['output'])) {
            $invoice->output(
                new Output(
                    format: $this->getOutputFormat(),
                    content: $data['output'],
                    fileName: $data['number']
                )
            );
        }

        return $invoice;
    }

    /**
     * Get the payload to send to the provider
     *
     * @return Collection<string, mixed>
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
        $this->payload->put('type', $this->getType()->value);
    }

    protected function buildDueDate(): void
    {
        if (! $this->getDueDate()) {
            return;
        }

        throw_if(
            $this->getType() !== InvoiceType::Invoice,
            Exception::class,
            'Due date can only be set for FT document types.'
        );

        $this->payload->put('due_date', $this->getDueDate()->toDateString());
    }

    protected function buildOutput(): void
    {
        $this->payload->put('output', $this->getOutputFormat()->vendus());
    }

    protected function buildTransport(): void
    {
        if (! $this->getTransport()) {
            return;
        }

        if (! $this->getClient()) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(
            is_null($this->getTransport()->origin()->getDate()),
            NeedsDateToSetLoadPointException::class
        );

        $data = [];

        $data['loadpoint'] = [
            'date' => $this->getTransport()->origin()->getDate()->toDateString(),
            'time' => $this->getTransport()->origin()->getTime()->format('H:i'),
            'address' => $this->getTransport()->origin()->getAddress(),
            'postalcode' => $this->getTransport()->origin()->getPostalCode(),
            'city' => $this->getTransport()->origin()->getCity(),
            'country' => $this->getTransport()->origin()->getCountry(),
        ];

        $landpointData = [
            'address' => $this->getTransport()->destination()->getAddress(),
            'postalcode' => $this->getTransport()->destination()->getPostalCode(),
            'city' => $this->getTransport()->destination()->getCity(),
            'country' => $this->getTransport()->destination()->getCountry(),
        ];

        if ($this->getTransport()->destination()->getDate()) {
            $landpointData['date'] = $this->getTransport()->destination()->getDate()->toDateString();
            $landpointData['time'] = $this->getTransport()->destination()->getTime()->format('H:i');
        }

        $data['landpoint'] = $landpointData;

        if ($this->getTransport()->getVehicleLicensePlate()) {
            $data['vehicle_id'] = $this->getTransport()->getVehicleLicensePlate();
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
        if ($this->getType() !== InvoiceType::CreditNote) {
            $this->payload->put('related_document_id', (int) $this->getRelatedDocument());

            return;
        }
    }

    protected function buildPayments(): void
    {
        if ($this->getPayments()->isEmpty()) {
            return;
        }

        $payments = $this->getPayments()->map(function (Payment $payment) {
            $id = $this->getConfig()->get('payments')[$payment->getMethod()->value] ?? null;

            throw_if(! $id, Exception::class, 'Payment method not configured.');

            return [
                'amount' => (float) ($payment->getAmount() / 100),
                'id' => $id,
            ];
        });

        $this->payload->put('payments', $payments);
    }

    protected function buildItems(): void
    {
        if ($this->getType() === InvoiceType::Receipt) {
            return;
        }

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $this->getItems()->map(function (Item $item): array {
            $data = [];

            if ($item->getReference()) {
                $data['reference'] = $item->getReference();
            }

            if ($item->getPrice()) {
                $data['gross_price'] = $item->getPrice() / 100;
            }

            if ($item->getQuantity()) {
                $data['qty'] = $item->getQuantity();
            }

            if ($item->getNote()) {
                $data['note'] = $item->getNote();
            }

            if ($item->getType()) {
                $data['type_id'] = $item->getType()->vendus();
            }

            if ($item->getPercentageDiscount()) {
                $data['discount_percent'] = $item->getPercentageDiscount();
            }

            if ($item->getAmountDiscount()) {
                $data['discount_amount'] = $item->getAmountDiscount() / 100;
            }

            if ($item->getTax()) {
                $data['tax_id'] = $item->getTax()->vendus();
            }

            if ($item->getTaxExemption()) {
                $data['tax_exemption'] = $item->getTaxExemption()->value;

                if ($item->getTaxExemptionLaw()) {
                    $data['tax_exemption_law'] = $item->getTaxExemptionLaw();
                }
            }

            if ($this->getType() === InvoiceType::CreditNote) {
                throw_if(
                    ! $item->getRelatedDocument(),
                    MissingRelatedDocumentException::class
                );

                $data['reference_document'] = [
                    'document_number' => $item->getRelatedDocument()->getDocumentId(),
                    'document_row' => $item->getRelatedDocument()->getRow(),
                ];
            }

            return $data;
        });

        if ($items->isEmpty()) {
            return;
        }

        $this->payload->put('items', $items);
    }

    protected function buildClient(): void
    {
        if (! $this->getClient()) {
            return;
        }

        throw_if(
            ! is_null($this->getClient()->getVat()) &&
                empty($this->getClient()->getVat()),
            InvoiceRequiresClientVatException::class
        );

        throw_if(
            $this->getClient()->getName() &&
                ! $this->getClient()->getVat(),
            InvoiceRequiresVatWhenClientHasName::class
        );

        $this->getClient()->getId() && $data['id'] = $this->getClient()->getId();
        $this->getClient()->getName() && $data['name'] = $this->getClient()->getName();
        $this->getClient()->getVat() && $data['fiscal_id'] = $this->getClient()->getVat();
        $this->getClient()->getAddress() && $data['address'] = $this->getClient()->getAddress();
        $this->getClient()->getCity() && $data['city'] = $this->getClient()->getCity();
        $this->getClient()->getPostalCode() && $data['postalcode'] = $this->getClient()->getPostalCode();
        $this->getClient()->getCountry() && $data['country'] = $this->getClient()->getCountry();
        $this->getClient()->getEmail() && $data['email'] = $this->getClient()->getEmail();
        $this->getClient()->getPhone() && $data['phone'] = $this->getClient()->getPhone();

        $retention = $this->getClient()->getIrsRetention();
        $data['irs_retention'] = $retention ? 'yes' : 'no';

        $this->payload->put('client', $data);
    }
}
