<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use Illuminate\Support\Collection;

interface CreateInvoice {
    public function invoice () : Invoice;

    public function client(Client $client) : self ;

    public function item(Item $item) : self ;

    public function payment(Payment $payments) : self ;

    public function transport(TransportDetails $transport) : self ;
    
    public function getPayload(): Collection;
    
    public function getClient() : ?Client ;

    public function getItems() : Collection ;

    public function getPayments() : Collection ;

    public function getTransport(): ?TransportDetails ;
}