<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\Transformer;

arch()->preset()->php();

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('strict types')
    ->expect('CsarCrr\InvoicingIntegration\Enums')
    ->expect('CsarCrr\InvoicingIntegration\Facades')
    ->expect('CsarCrr\InvoicingIntegration\Provider')
    ->expect('CsarCrr\InvoicingIntegration\Providers')
    ->expect('CsarCrr\InvoicingIntegration\Services')
    ->expect('CsarCrr\InvoicingIntegration\Traits')
    ->expect('CsarCrr\InvoicingIntegration\Transformers')
    ->expect('CsarCrr\InvoicingIntegration\Actions')
    ->expect('CsarCrr\InvoicingIntegration\Data')
    ->expect('CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider')
    ->toUseStrictTypes();

arch('final classes')
    ->expect('CsarCrr\InvoicingIntegration\Configuration')
    ->expect('CsarCrr\InvoicingIntegration\Actions')
    ->toBeFinal();

arch('facades')
    ->expect('CsarCrr\InvoicingIntegration\Facades')
    ->toExtend(Facade::class)
    ->not->toBeUsedIn('CsarCrr\InvoiceIntegration\*\*');

arch('contracts')
    ->expect('CsarCrr\InvoicingIntegration\Contracts')
    ->toBeInterfaces();

arch('enums')
    ->expect('CsarCrr\InvoicingIntegration\Enums')
    ->toBeEnums();

arch('traits')
    ->expect('CsarCrr\InvoicingIntegration\Traits')
    ->toBeTraits();

arch('data')
    ->expect('CsarCrr\InvoicingIntegration\Data')
    ->toBeClasses()
    ->toExtend(Data::class)
    ->toBeUsedIn('CsarCrr\InvoicingIntegration\Provider');

arch('transformers')
    ->expect('CsarCrr\InvoicingIntegration\Transformers')
    ->toBeClasses()
    ->toExtend(Transformer::class);
