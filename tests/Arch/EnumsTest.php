<?php

declare(strict_types=1);

arch('enums')
    ->expect('CsarCrr\InvoicingIntegration\Enums')
    ->toBeEnums()
    ->toExtendNothing()
    ->toUse([
        'CsarCrr\InvoicingIntegration\Traits\EnumOptions'
    ]);
