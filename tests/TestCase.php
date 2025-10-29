<?php

namespace CsarCrr\InvoicingIntegration\Tests;

use CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'VendorName\\Skeleton\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app)
    {
        return [
            InvoicingIntegrationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
