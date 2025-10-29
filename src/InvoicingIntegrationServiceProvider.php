<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Commands\InvoicingIntegrationCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        $this->app->bind('invoicing-integration', function () {
            $config = config('invoicing-integration');

            return new InvoicingIntegration(
                key: $config['vendus'],
                mode: $config['test_mode'] ? 'test' : 'live'
            );
        });
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
