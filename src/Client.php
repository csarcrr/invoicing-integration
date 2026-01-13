<?php 

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;

final class Invoice
{
    protected string $action;

    public function __construct(
        protected IntegrationProvider $provider
    ) {}

    public static function create(): CreateClient
    {
        $class = app()->make(self::class);

        return $class->provider(Action::CREATE);
    }

    public function provider(Action $action): mixed
    {
        return match ($this->provider) {
            IntegrationProvider::CEGID_VENDUS => CegidVendus::client($action)
        };
    }
}