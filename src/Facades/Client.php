<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\ClientAction;
use Illuminate\Support\Facades\Facade;

class Client extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ClientAction::class;
    }
}
