<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\ClientAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldGetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreateClient create(ClientData $client)
 * @method static ShouldGetClient get(ClientData $client)
 * @method static ShouldFindClient find(?ClientData $client = null)
 *
 * @see ClientAction
 */
class Client extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ClientAction::class;
    }
}
