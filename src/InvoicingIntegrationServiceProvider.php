<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Providers\Vendus;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        $this->app->bind('invoicing-integration', function () {
            $config = config('invoicing-integration');

            $this->guardAgainstInvalidConfig($config);

            return new InvoicingIntegration($config['provider']);
        });

        $this->app->singleton('vendus', function () {
            $config = config('invoicing-integration');

            $this->guardAgainstInvalidProviderConfig($config['providers'][$config['provider']]);
            $this->guardAgainstMissingPaymentDetails($config['providers'][$config['provider']]['config']['payments']);

            return new Vendus(
                apiKey: $config['providers'][$config['provider']]['key'],
                mode: $config['providers'][$config['provider']]['mode'],
                options: collect($config['providers'][$config['provider']]['config']),
            );
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoicing-integration')
            ->hasConfigFile('invoicing-integration');
    }

    protected function guardAgainstInvalidConfig(array $config): void
    {
        if (! isset($config['provider'])) {
            throw new \InvalidArgumentException('A provider is needed to use the Invoicing Integration package.');
        }

        if (! isset($config['providers'][$config['provider']])) {
            throw new \InvalidArgumentException("The specified provider [{$config['provider']}] is not configured.");
        }
    }

    protected function guardAgainstInvalidProviderConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            if (is_null($value)) {
                throw new \InvalidArgumentException("The provider configuration is missing the required key: {$key}.");
            }
        }
    }

    protected function guardAgainstMissingPaymentDetails(array $payments): void
    {
        foreach ($payments as $key => $value) {
            if (!is_null($value)) {
                return;
            }
        }

        throw new \Exception('The provider configuration is missing payment method details.');
    }
}
