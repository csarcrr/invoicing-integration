<?php

arch()->preset()->php();

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('strict types')
    ->expect('CsarCrr\InvoicingIntegration')
    ->toUseStrictTypes();