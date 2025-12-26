<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->item = new Item;
});

it('can assign a related document', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    expect($this->item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($this->item->relatedDocument()->get('row'))->toBe(1);
});

it('can override the line of a related document', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    expect($this->item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($this->item->relatedDocument()->get('row'))->toBe(1);

    $this->item->setRelatedDocument('FT 01P2025/1', 2);

    expect($this->item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($this->item->relatedDocument()->get('row'))->toBe(2);
});

it('is empty when no related document was set', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    expect($this->item->relatedDocument()->isEmpty())->toBeTrue();
});
