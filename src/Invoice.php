<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;

class Invoice
{
    protected string $action;

    public function __construct(
        protected IntegrationProvider $provider
    ) {}

    static public function create(): CreateInvoice
    {
        $class = app()->make(self::class);
        return $class->provider(Action::CREATE);
    }

    public function provider(Action $action): mixed
    {
        return match ($this->provider) {
            IntegrationProvider::CEGID_VENDUS => CegidVendus::invoice($action)
        };
    }
}
