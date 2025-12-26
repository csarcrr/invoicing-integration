<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
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

    public function __construct()
    {
        $this->payload = collect();
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
    public function payload(): Collection
    {
        return $this->payload;
    }

    public function client(Client $client): self
    {
        $this->client = $client;
        
        $this->buildClient();

        return $this;
    }

    public function item(Item $items): self
    {
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
