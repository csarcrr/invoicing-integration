<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Create;

final class Invoice
{
    protected string $action;

    public function __construct(
        protected Provider $provider
    ) {}

    public static function create(): CreateInvoice
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            Provider::CEGID_VENDUS => new Create(Provider::CEGID_VENDUS->config()),
        };
    }
}
