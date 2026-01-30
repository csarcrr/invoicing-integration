<?php

namespace CsarCrr\InvoicingIntegration\Configuration;

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ProviderConfiguration;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class HttpConfiguration
{
    public function __construct()
    {

    }

    public static function get(): Factory|PendingRequest
    {
        return match (ProviderConfiguration::getProvider()) {
            Provider::CEGID_VENDUS => self::cegidVendus(),
        };
    }

    private static function cegidVendus(): Factory|PendingRequest
    {
        $config = ProviderConfiguration::getConfig();

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['key'],
        ])
            ->baseUrl('https://www.vendus.pt/ws/v1.1/')
            ->timeout(30)
            ->connectTimeout(10);
    }
}
