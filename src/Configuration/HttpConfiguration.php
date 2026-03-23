<?php

namespace CsarCrr\InvoicingIntegration\Configuration;

use CsarCrr\InvoicingIntegration\Configuration\Authentication\SolveMoloniAuthentication;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ProviderConfiguration;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class HttpConfiguration
{
    public function __construct() {}

    public static function get(): PendingRequest
    {
        return match (ProviderConfiguration::getProvider()) {
            Provider::CEGID_VENDUS => self::cegidVendus(),
            Provider::MOLONI => self::moloni(),
        };
    }

    private static function cegidVendus(): PendingRequest
    {
        $config = ProviderConfiguration::getConfig();

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$config['key'],
        ])
            ->baseUrl('https://www.vendus.pt/ws/v1.1/')
            ->timeout(30)
            ->connectTimeout(10);
    }

    private static function moloni () : PendingRequest {
        $config = ProviderConfiguration::getConfig();

        $auth = (new SolveMoloniAuthentication($config))->execute()->getPayload();

        return Http::withHeaders([
            'Authorization' => 'Bearer '. $auth['access_token'],
        ])
            ->baseUrl('https://api.moloni.pt/v1/')
            ->timeout(30)
            ->connectTimeout(10);
    }
}
