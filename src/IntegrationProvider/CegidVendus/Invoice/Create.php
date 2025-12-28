<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Traits\ProviderConfiguration;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use Exception;
use Illuminate\Support\Collection;

class Create implements CreateInvoice
{
    use ProviderConfiguration; 

    protected Collection $payload;

    protected Collection $items;
    protected Collection $payments;
    protected ?Client $client = null;
    protected ?TransportDetails $transport = null;
    protected InvoiceType $type = InvoiceType::Invoice;

    protected array $invoiceTypesThatRequirePayments = [
        InvoiceType::Receipt,
        InvoiceType::InvoiceReceipt,
        InvoiceType::InvoiceSimple,
        InvoiceType::CreditNote,
    ];

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
    public function invoice(): Invoice
    {
        return new Invoice();
    }

    /**
     * Get the payload to send to the provider
     */
    public function getPayload(): Collection
    {
        $this->buildClient();
        $this->buildItems();
        $this->buildPayments();
        $this->buildTransport();
        
        return $this->payload;
    }

    public function type(InvoiceType $type): self
    {
        $this->type = $type;
        $this->payload->put('type', $type->value);

        return $this;
    }

    public function client(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function item(Item $items): self
    {
        $this->items->push($items);

        return $this;
    }

    public function payment(Payment $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    public function transport(TransportDetails $transport): self
    {
        $this->transport= $transport;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getTransport(): ?TransportDetails
    {
        return $this->transport;
    }

    public function getType(): InvoiceType
    {
        return $this->type;
    }

    protected function buildTransport(): void {
        if (!$this->getTransport()) {
            return;
        }

        if (! $this->getClient()) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(
            ! in_array($this->getType(), [InvoiceType::Invoice, InvoiceType::Transport]),
            InvoiceTypeDoesNotSupportTransportException::class
        );

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

    protected function buildPayments (): void {

        throw_if(
            in_array(
                $this->getType(),
                $this->invoiceTypesThatRequirePayments
            ) && $this->getPayments()->isEmpty(),
            MissingPaymentWhenIssuingReceiptException::class,
        );

        if ($this->getPayments()->isEmpty()) {
            return;
        }

        $payments = $this->getPayments()->map(function (Payment $payment) {
            $id = $this->getConfig()->get('payments')[$payment->getMethod()->value] ?? null;

            throw_if(!$id, Exception::class, 'Payment method not configured.');

            return [
                'amount' => (float) ($payment->getAmount() / 100),
                'id' => $id,
            ];
        });

        $this->payload->put('payments', $payments);
    }

    protected function buildItems(): void {
        if ($this->getType() === InvoiceType::Receipt) {
            return;
        }

        throw_if(
            $this->getItems()->isEmpty(),
            InvoiceItemIsNotValidException::class,
            'The invoice must have at least one item.'
        );

        $items = $this->getItems()->map(function (Item $item) {
            $data= [];
            
            if($item->getReference()) {
                $data['reference'] = $item->getReference();
            }

            if($item->getPrice()) {
                $data['price'] = $item->getPrice() / 100;
            }

            if($item->getQuantity()) {
                $data['qty'] = $item->getQuantity();
            }

            if($item->getNote()) {
                $data['note'] = $item->getNote();
            }

            if($item->getType()) {
                $data['type_id'] = $item->getType()->vendus();
            }

            if($item->getPercentageDiscount()) {
                $data['discount_percent'] = $item->getPercentageDiscount();
            }

            if($item->getAmountDiscount()) {
                $data['discount_amount'] = $item->getAmountDiscount() / 100;
            }

            if($item->getTax()) {
                $data['tax_id'] = $item->getTax()->vendus();
            }

            if($item->getTaxExemption()) {
                $data['tax_exemption'] = $item->getTaxExemption()->value;

                if ($item->getTaxExemptionLaw()) {
                    $data['tax_exemption_law'] = $item->getTaxExemptionLaw();
                }
            }

            if($this->getType() === InvoiceType::CreditNote) {
                throw_if(
                    $item->getRelatedDocument()->isEmpty(),
                    InvoiceItemIsNotValidException::class,
                    'Credit Note items must have a related document set.'
                );

                $data['reference_document'] = [
                    'document_number' => $item->getRelatedDocument()->get('document_id'),
                    'document_row' => $item->getRelatedDocument()->get('row'),
                ];
            }

            return $data;
        });

        $this->payload->put('items', $items);
    }

    protected function buildClient(): void
    {
        if (!$this->getClient()) {
            return;
        }

        throw_if(
            ! is_null($this->getClient()->vat) &&
                empty($this->getClient()->vat),
            InvoiceRequiresClientVatException::class
        );

        throw_if(
            $this->getClient()->name &&
                ! $this->getClient()->vat,
            InvoiceRequiresVatWhenClientHasName::class
        );

        $data = [
            'name' => $this->getClient()->name,
            'fiscal_id' => $this->getClient()->vat,
        ];

        if ($this->getClient()->getAddress()) {
            $data['address'] = $this->getClient()->getAddress();
        }

        if ($this->getClient()->getCity()) {
            $data['city'] = $this->getClient()->getCity();
        }

        if ($this->getClient()->getPostalCode()) {
            $data['postalcode'] = $this->getClient()->getPostalCode();
        }

        if ($this->getClient()->getCountry()) {
            $data['country'] = $this->getClient()->getCountry();
        }

        if ($this->getClient()->getEmail()) {
            $data['email'] = $this->getClient()->getEmail();
        }

        if ($this->getClient()->getPhone()) {
            $data['phone'] = $this->getClient()->getPhone();
        }

        if (! is_null($this->getClient()->getIrsRetention())) {
            $retention = $this->getClient()->getIrsRetention();
            $data['irs_retention'] = $retention ? 'yes' : 'no';
        }

        $this->payload->put('client', $data);
    }
}
