<?php

declare(strict_types=1);

arch('exceptions')
    ->expect('CsarCrr\InvoicingIntegration\Exceptions')
    ->toImplement('Throwable')
    ->toOnlyBeUsedIn([
        'CsarCrr\InvoicingIntegration',
    ]);
