<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

class Create implements CreateInvoice
{
    protected Collection $payload;
    protected Collection $items;
    protected Collection $payments;
    protected ?Client $client = null;
    protected InvoiceType $type = InvoiceType::Invoice;

    public function __construct()
    {
        $this->payload = collect([
            'type' => $this->getType()->value,
        ]);
        $this->items = collect();
        $this->payments = collect();
    }

    static public function create()
    {
        return app()->make(self::class);
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

    public function payment(Payment $payments): self
    {
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

    public function getType(): InvoiceType
    {
        return $this->type;
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
