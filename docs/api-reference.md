# API Reference

## Main Classes


### InvoicingIntegration

-   `__construct(string $provider)` — Create a new invoicing integration instance with the specified provider.
-   `create(): self` — Initialize the integration (returns self for method chaining).
-   `setClient(Client $client): self` — Set the client for the invoice. **Optional for final consumer invoices:** do not call this method if invoicing to a final consumer.
-   `addItem(Item $item): self` — Add an item to the invoice.
-   `addPayment(Payment $payment): self` — Add a payment to the invoice.
-   `setType(InvoiceType $type): self` — Set the document type.
-   `setDate(Carbon $date): self` — Set the invoice date (Default: ``date('Y-m-d')``).
-   `setDueDate(Carbon $dateDue): self` — Set the due date (cannot be in the past).
-   `addRelatedDocument(string $relatedDocument): self` — Add a related document.
-   `setTransport(?TransportDetails $transport): self` — Set transport details.
-   `asEscPos(): self` — Sets the output format type to ESCPOS.
-   `invoice(): InvoiceData` — Generate and send the invoice.
-   `get(): self` — Returns the current instance.

**Getter Methods:**
-   `client(): ?Client` — Get the current client.
-   `payments(): Collection` — Get all payments.
-   `items(): Collection` — Get all items.
-   `relatedDocuments(): Collection` — Get all related documents.
-   `type(): InvoiceType` — Get the document type.
-   `date(): Carbon` — Get the invoice date.
-   `dueDate(): ?Carbon` — Get the due date.
-   `transport(): ?TransportDetails` — Get transport details.
-   `outputFormat(): OutputFormat` — Get the current output format.

> **Note:** For final consumer invoices, do not set any client information (do not call `setClient`).


### Client

**Constructor:**
-   `__construct(?string $vat = null, ?string $name = null)` — Create a new client with optional VAT and name.

**Public Properties:**
-   `$vat` — Client VAT number.
-   `$name` — Client name.

**Getter Methods:**
-   `vat(): ?string` — Get the VAT number.
-   `name(): ?string` — Get the client name.
-   `address(): ?string` — Get the address.
-   `city(): ?string` — Get the city.
-   `postalCode(): ?string` — Get the postal code.
-   `country(): ?string` — Get the country.
-   `email(): ?string` — Get the email.
-   `phone(): ?string` — Get the phone number.

**Setter Methods:**
-   `setVat(?string $vat): void` — Set the VAT number.
-   `setName(?string $name): void` — Set the client name.
-   `setAddress(?string $address): void` — Set the address.
-   `setCity(?string $city): void` — Set the city.
-   `setPostalCode(?string $postalCode): void` — Set the postal code.
-   `setCountry(?string $country): void` — Set the country.
-   `setEmail(?string $email): void` — Set the email.
-   `setPhone(?string $phone): void` — Set the phone number.

> **Note:** Only required for non-final-consumer invoices.


### Item

**Constructor:**
-   `__construct(null|int|string $reference = null, ?int $quantity = null)` — Create a new item with optional reference and quantity (defaults to 1).

**Getter Methods:**
-   `reference(): int|string` — Get the product reference.
-   `quantity(): int` — Get the quantity.
-   `price(): ?int` — Get the price (in cents).
-   `note(): ?string` — Get the note/description.
-   `type(): ?ItemType` — Get the item type.
-   `tax(): ?DocumentItemTax` — Get the tax type.
-   `taxExemption(): ?TaxExemptionReason` — Get the tax exemption reason.
-   `taxExemptionLaw(): ?string` — Get the tax exemption law.
-   `amountDiscount(): ?int` — Get the amount discount.
-   `percentageDiscount(): ?int` — Get the percentage discount.

**Setter Methods:**
-   `setReference(int|string $reference): void` — Set the product reference.
-   `setQuantity(int $quantity): void` — Set the quantity.
-   `setPrice(int $price): void` — Set price (in cents).
-   `setNote(string $note): void` — Set item description/note.
-   `setType(ItemType $type): self` — Set the item type.
-   `setTax(?DocumentItemTax $tax = null): self` — Set tax type.
-   `setTaxExemption(?TaxExemptionReason $reason = null): self` — Set tax exemption reason (only for exempt tax).
-   `setTaxExemptionLaw(string $law): self` — Set exemption law (requires exemption reason).
-   `setAmountDiscount(int $amount): self` — Set amount discount.
-   `setPercentageDiscount(int $percent): self` — Set percentage discount.

> **Note:** At least one item is required for every invoice (except receipts).


### Payment

**Constructor:**
-   `__construct(?PaymentMethod $method = null, ?int $amount = null)` — Create a new payment with optional method and amount.

**Getter Methods:**
-   `method(): ?PaymentMethod` — Get the payment method.
-   `amount(): ?int` — Get the payment amount (in cents).

**Setter Methods:**
-   `setMethod(PaymentMethod $method): self` — Set the payment method.
-   `setAmount(int $amount): self` — Set the payment amount (in cents).

> **Note:** At least one payment is required for every invoice.


### TransportDetails

**Methods:**
-   `origin(): self` — Set the context to origin for subsequent operations.
-   `destination(): self` — Set the context to destination for subsequent operations.
-   `vehicleLicensePlate(?string $vehicleLicensePlate = null): ?string` — Get or set the vehicle license plate.

> **Note:** Used for transport documents and logistics information.


### InvoiceData

The return type from the `invoice()` method containing the generated invoice information.

**Methods:**
-   `id(): int` — Get the invoice ID.
-   `sequence(): string` — Get the invoice sequence number.
-   `output(): Output` — Get the output data (PDF, ESCPOS, etc.).
-   `setId(int $id): self` — Set the invoice ID.
-   `setSequence(string $sequence): self` — Set the sequence number.
-   `setOutput(Output $output): self` — Set the output data.


## Enums


### InvoiceType

Available document types:
-   `InvoiceType::Invoice` — Regular invoice (FT)
-   `InvoiceType::InvoiceReceipt` — Invoice receipt (FR)
-   `InvoiceType::InvoiceSimple` — Simplified invoice (FS)
-   `InvoiceType::Receipt` — Receipt (RG)
-   `InvoiceType::Transport` — Transport document (GT)
-   `InvoiceType::CreditNote` — Credit note (NC)


### PaymentMethod

Available payment methods:
-   `PaymentMethod::MONEY` — Cash payment
-   `PaymentMethod::MB` — ATM/Debit card
-   `PaymentMethod::CREDIT_CARD` — Credit card
-   `PaymentMethod::MONEY_TRANSFER` — Bank transfer
-   `PaymentMethod::CURRENT_ACCOUNT` — Current account


### DocumentItemTax

Available tax rates:
-   `DocumentItemTax::NORMAL` — Normal tax rate
-   `DocumentItemTax::INTERMEDIATE` — Intermediate tax rate
-   `DocumentItemTax::REDUCED` — Reduced tax rate
-   `DocumentItemTax::EXEMPT` — Tax exempt
-   `DocumentItemTax::OTHER` — Other tax rate


### TaxExemptionReason

Tax exemption reasons (Portuguese tax system codes):
-   `TaxExemptionReason::M01` through `TaxExemptionReason::M30` — Various exemption reasons as defined by Portuguese tax authority.

> **Note:** Use only when `DocumentItemTax::EXEMPT` is set on an item.

---

For more details, see the source code in the `src/` directory.
