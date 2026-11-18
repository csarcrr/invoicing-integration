<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;

it('has no type by default', function () {
    expect(InvoiceData::make([])->type)->toBeNull();
});
