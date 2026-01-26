<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Tests;

use CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'VendorName\\Skeleton\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [
            InvoicingIntegrationServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
