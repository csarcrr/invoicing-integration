<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->item = new InvoiceItem;
});

it('can assign quantity', function () {
    $this->item->setReference('reference-1');
    $this->item->setQuantity(3);
    $this->item->setPrice(500);

    expect($this->item->quantity())->toBe(3);
});

it('can assign quantity with setter', function () {
    $this->item->setReference('reference-1');
    $this->item->setQuantity(5);

    expect($this->item->quantity())->toBe(5);
});

it('defaults quantity to 1', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->quantity())->toBe(1);
});
