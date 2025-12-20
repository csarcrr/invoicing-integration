<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->item = new InvoiceItem;
});

it('can assign reference', function () {
    $this->item->setReference('reference-1');

    expect($this->item->reference())->toBe('reference-1');
});

it('can assign reference with setter', function () {
    $this->item->setReference('reference-1');

    expect($this->item->reference())->toBe('reference-1');
});
