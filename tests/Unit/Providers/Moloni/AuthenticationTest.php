<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Configuration\Authentication\SolveMoloniAuthentication;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('cache.default', 'array');
    mockConfiguration(Provider::MOLONI);
    Cache::flush();
});

it('fetches a token from Moloni and returns it in the payload', function () {
    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('fresh-access-token');
});

it('builds the correct OAuth URL with all four query params', function () {
    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    (new SolveMoloniAuthentication($config))->execute();

    Http::assertSent(function ($request) use ($config) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['grant_type'] === 'password'
            && $query['client_id'] === $config['developer_id']
            && $query['client_secret'] === $config['client_secret']
            && $query['username'] === $config['username']
            && $query['password'] === $config['password'];
    });
});

/**
 * access_token is solved by Pest.php -> mockConfiguration
 */
it('caches the token after a successful fetch', function () {
    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    (new SolveMoloniAuthentication($config))->execute();

    expect(Cache::has('invoicing_integration_moloni_access_token'))->toBeTrue()
        ->and(Cache::get('invoicing_integration_moloni_access_token'))->toBe(['access_token' => 'fresh-access-token']);
});

it('throws the Moloni error when the grant request fails', function () {
    Http::swap(new Factory);
    Http::fake([
        'api.moloni.pt/v1/grant/*' => mockResponse([
            'error' => 'invalid_grant',
            'error_description' => 'Invalid username and password combination',
        ], 400),
    ]);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);

    expect(fn () => (new SolveMoloniAuthentication($config))->execute())
        ->toThrow(RequestFailedException::class, 'invalid_grant: Invalid username and password combination');

    expect(Cache::has('invoicing_integration_moloni_access_token'))->toBeFalse();
});

it('reuses the cached token without making a new HTTP request', function () {
    Cache::put('invoicing_integration_moloni_access_token', ['access_token' => 'cached-token'], 3600);

    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('cached-token');
    Http::assertNothingSent();
});

it('fetches a fresh token when the cache is empty', function () {
    $config = config('invoicing-integration.providers.'.Provider::MOLONI->value);
    $auth = (new SolveMoloniAuthentication($config))->execute();

    expect($auth->getPayload()->get('access_token'))->toBe('fresh-access-token');
    Http::assertSentCount(1);
});
