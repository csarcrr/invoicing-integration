<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Actions\ClientAction;
use CsarCrr\InvoicingIntegration\Actions\InvoiceAction;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        Http::macro('provider', function () {
            $provider = Provider::from(config('invoicing-integration.provider'));

            return match ($provider) {
                Provider::CEGID_VENDUS => CegidVendus::setupHttpConfiguration()
            };
        });

        Http::macro('handleUnwantedFailures', function (Response $response) {
            $status = $response->status();

            if (in_array($status, [200, 201, 300, 301])) {
                return;
            }

            throw_if($status === 500, FailedReachingProviderException::class);
            throw_if($status === 401, UnauthorizedException::class);

            $body = $response->json();
            /** @var array<int, array{code?: string, message?: string}> $errorList */
            $errorList = $body['errors'] ?? [];

            $messages = collect($errorList)->map(function (array $error): string {
                return isset($error['message']) ? ($error['code'] ?? '').' - '.$error['message'] : 'Unknown error';
            })->toArray();

            throw_if(! empty($messages), RequestFailedException::class, implode('; ', $messages));

            throw new Exception('The integration API request failed for an unknown reason.');
        });

        $this->app->when([InvoiceAction::class, ClientAction::class])
            ->needs(Provider::class)
            ->give(function () {
                return Provider::from(config('invoicing-integration.provider'));
            });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoicing-integration')
            ->hasConfigFile('invoicing-integration');
    }
}
