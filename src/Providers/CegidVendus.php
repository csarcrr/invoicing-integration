<?php 

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;

class CegidVendus {
    protected string $request = 'invoice';

    static public function invoice(Action $action): mixed
     {
        $provider = new self();
        
        return match($action) {
            Action::CREATE => $provider->create(),
        };
    }

    public function create (): CreateInvoice {
        return Create::create();
    }
}