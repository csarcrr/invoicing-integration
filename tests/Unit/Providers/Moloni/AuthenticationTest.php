<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Configuration\Authentication\SolveMoloniAuthentication;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('cache.default', 'array');
    mockConfiguration(Provider::MOLONI);
    Cache::flush();
});

it('fetches a token from Moloni and returns it in the payload', function () {
    Http::fake([
        'api.moloni.pt/*' => mockResponse([
            'access_token' => 'test-access-token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('test-access-token');
});

it('builds the correct OAuth URL with all four query params', function () {
    Http::fake([
        'api.moloni.pt/*' => mockResponse([
            'access_token' => 'test-access-token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    (new SolveMoloniAuthentication($config))->execute();

    Http::assertSent(function ($request) use ($config) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['grant_type'] === 'authorization_code'
            && $query['client_id'] === $config['developer_id']
            && $query['redirect_uri'] === $config['callback_url']
            && $query['client_secret'] === $config['client_secret']
            && $query['code'] === $config['authorization_code'];
    });
});

it('caches the token after a successful fetch', function () {
    Http::fake([
        'api.moloni.pt/*' => mockResponse([
            'access_token' => 'test-access-token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    (new SolveMoloniAuthentication($config))->execute();

    expect(Cache::has('invoicing_integration_moloni_access_token'))->toBeTrue();
    expect(Cache::get('invoicing_integration_moloni_access_token'))->toMatchArray(['access_token' => 'test-access-token']);
});

it('reuses the cached token without making a new HTTP request', function () {
    Http::fake([
        'api.moloni.pt/*' => mockResponse([
            'access_token' => 'new-access-token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    Cache::put('invoicing_integration_moloni_access_token', ['access_token' => 'cached-token'], 3600);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('cached-token');
    Http::assertNothingSent();
});

it('fetches a fresh token when the cache is empty', function () {
    Http::fake([
        'api.moloni.pt/*' => mockResponse([
            'access_token' => 'fresh-access-token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('fresh-access-token');
    Http::assertSentCount(1);
});
