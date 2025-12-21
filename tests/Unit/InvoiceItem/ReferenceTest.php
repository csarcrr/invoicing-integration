<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->item = new Item;
});

it('can assign reference', function () {
    $this->item->setReference('reference-1');

    expect($this->item->reference())->toBe('reference-1');
});

it('can assign reference with setter', function () {
    $this->item->setReference('reference-1');

    expect($this->item->reference())->toBe('reference-1');
});
