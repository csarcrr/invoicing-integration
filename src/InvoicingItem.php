<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;

class InvoicingItem
{
    protected ?int $price = null;

    protected ?string $description = null;

    protected DocumentItemType $type;

    /**
     * @param  string  $reference  - avoids duplicate products in some providers
     */
    public function __construct(
        public string $reference,
        public int $quantity = 1,
    ) {
        $this->type = DocumentItemType::Product;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function price(): ?int
    {
        return $this->price;
    }

    public function type(): DocumentItemType
    {
        return $this->type;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setType(DocumentItemType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
