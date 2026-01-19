<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Invoice\Create;

final class Invoice
{
    protected string $action;

    public function __construct(
        protected IntegrationProvider $provider
    ) {}

    public static function create(): CreateInvoice
    {
        $class = app()->make(self::class);

        return match ($class->provider) {
            IntegrationProvider::CEGID_VENDUS => new Create(IntegrationProvider::CEGID_VENDUS->config()),
        };
    }
}
