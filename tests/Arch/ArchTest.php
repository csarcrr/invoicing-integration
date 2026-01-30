<?php

declare(strict_types=1);

arch()->preset()->php();

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('strict types')
    ->expect('CsarCrr\InvoicingIntegration\ValueObjects')
    ->expect('CsarCrr\InvoicingIntegration\Enums')
    ->expect('CsarCrr\InvoicingIntegration\Facades')
    ->expect('CsarCrr\InvoicingIntegration\Provider')
    ->expect('CsarCrr\InvoicingIntegration\Providers')
    ->expect('CsarCrr\InvoicingIntegration\Services')
    ->expect('CsarCrr\InvoicingIntegration\Traits')
    ->expect('CsarCrr\InvoicingIntegration\Transformers')
    ->expect('CsarCrr\InvoicingIntegration\ValueObjects')
    ->expect('CsarCrr\InvoicingIntegration\Actions')
    ->expect('CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider')
    ->toUseStrictTypes();
