<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Tests;

use CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider;
use Illuminate\Support\Facades\Http;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [
            InvoicingIntegrationServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
