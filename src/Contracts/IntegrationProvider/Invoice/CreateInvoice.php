<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

interface CreateInvoice {
    static public function create () ;

    public function invoice () : Invoice;

    public function client(Client $client) : self ;

    public function item(Item $item) : self ;

    public function payment(Payment $payments) : self ;
    
    public function getPayload(): Collection;
    
    public function getClient() : ?Client ;

    public function getItems() : Collection ;

    public function getPayments() : Collection ;
}