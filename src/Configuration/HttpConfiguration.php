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
            Provider::MOLONI => self::Moloni(),
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

    /**
     * @throws \Throwable Failed obtaining access_token
     */
    private static function Moloni(): PendingRequest
    {
        $config = ProviderConfiguration::getConfig();
        $auth = (new SolveMoloniAuthentication($config))->execute()->getPayload();

        throw_if(empty($auth['access_token']), \Exception::class, 'Could not authenticate with Moloni');

        return Http::baseUrl('https://api.moloni.pt/v1/')
            ->withQueryParameters([
                'access_token' => $auth['access_token']
            ])
            ->withoutRedirecting()
            ->timeout(30)
            ->connectTimeout(10);
    }
}
