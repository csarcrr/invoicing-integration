<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->item = new Item;
});

it('can assign note', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setNote('Test note');

    expect($this->item->note())->toBe('Test note');
});

it('has note null by default', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->note())->toBeNull();
});
