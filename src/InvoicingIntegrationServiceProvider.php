<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        $this->app->when([Invoice::class, Client::class])
            ->needs(IntegrationProvider::class)
            ->give(function () {
                return IntegrationProvider::from(config('invoicing-integration.provider'));
            });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoicing-integration')
            ->hasConfigFile('invoicing-integration');
    }
}
