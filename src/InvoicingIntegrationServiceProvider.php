<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Configuration\HttpConfiguration;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function array_is_list;
use function collect;
use function implode;
use function in_array;
use function is_array;
use function throw_if;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        $this->setupHttpMacros();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoicing-integration')
            ->hasConfigFile('invoicing-integration');
    }

    private function setupHttpMacros(): void
    {
        Http::macro('provider', fn () => HttpConfiguration::get());

        Http::macro('handleUnwantedFailures', function (Response $response) {
            $status = $response->status();
            $body = $response->json();

            $validationErrors = collect(is_array($body) && array_is_list($body) ? $body : [])
                ->flatMap(fn (mixed $error): array => is_array($error) && array_is_list($error) ? $error : [$error])
                ->map(fn (mixed $error): ?string => is_array($error) && isset($error['code'], $error['description'])
                    ? "{$error['code']} - {$error['description']}"
                    : null);

            throw_if(
                $validationErrors->isNotEmpty() && ! $validationErrors->contains(null),
                RequestFailedException::class,
                $validationErrors->implode('; ')
            );

            if (in_array($status, [200, 201, 300, 301])) {
                return;
            }

            throw_if($status === 500, FailedReachingProviderException::class);
            throw_if($status === 401, UnauthorizedException::class);

            $messages = [];

            if (! empty($body['errors'])) {
                /** @var array<int, array{code?: string, message?: string}> $errors */
                $errors = $body['errors'];
                $messages = collect($errors)->map(function (array $error): string {
                    return isset($error['message']) ? ($error['code'] ?? '').' - '.$error['message'] : 'Unknown error';
                })->toArray();
            }

            if (! empty($body['error']) && ! empty($body['error_description'])) {
                $messages = ["{$body['error']}: {$body['error_description']}"];
            }

            throw_if(! empty($messages), RequestFailedException::class, implode('; ', $messages));

            Log::error('Failed handling unwanted failures.', [
                'response' => $body,
                'status' => $status,
            ]);

            throw new Exception('The integration API request failed for an unknown reason.');
        });
    }
}
