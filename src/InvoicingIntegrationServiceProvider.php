<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Actions\Invoice\Invoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Invoice as InvoicingIntegrationInvoice;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoicingIntegrationServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        $this->app->when(InvoicingIntegrationInvoice::class)
            ->needs(IntegrationProvider::class)
            ->give(function () {
                return IntegrationProvider::from(config('invoicing-integration.provider'));
            });

        $this->setupInvoiceBindings();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoicing-integration')
            ->hasConfigFile('invoicing-integration');
    }

    protected function setupInvoiceBindings(): void
    {
        $this->app->bind('invoice', function () {
            return new Invoice;
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
}
