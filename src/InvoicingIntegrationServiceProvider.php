<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Commands\InvoicingIntegrationCommand;
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

            return new Vendus(
                apiKey: $config['providers'][$config['provider']]['key'],
                mode: $config['providers'][$config['provider']]['mode'],
            );
        });
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

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('invoicing-integration')
            ->hasConfigFile()
            ->hasViews()
            // ->hasMigration('create_migration_table_name_table')
            ->hasCommand(InvoicingIntegrationCommand::class);
    }
}
