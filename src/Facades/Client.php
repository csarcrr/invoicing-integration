<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\ClientAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CreateClient create(ClientData $client)
 * @method static GetClient get(ClientData $client)
 * @method static FindClient find()
 *
 * @see \CsarCrr\InvoicingIntegration\ClientAction
 */
class Client extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ClientAction::class;
    }
}
