# API Reference

## Main Classes


### InvoicingIntegration

-   `setClient(InvoiceClient $client): self` — Set the client for the invoice. **Optional for final consumer invoices:** do not call this method if invoicing to a final consumer.
-   `addItem(InvoiceItem $item): self` — Add an item to the invoice.
-   `addPayment(InvoicePayment $payment): self` — Add a payment to the invoice.
-   `setType(DocumentType $type): self` — Set the document type.
-   `setDate(Carbon $date): self` — Set the invoice date.
-   `setDateDue(Carbon $dateDue): self` — Set the due date.
-   `addRelatedDocument(string $relatedDocument): self` — Add a related document.
-   `setTransport(InvoiceTransportDetails $transport): self` — Set transport details.
-   `asEscPos(): self` — Sets the output format type to ESCPOS.
-   `invoice(): InvoiceData` — Generate and send the invoice.

> **Note:** For final consumer invoices, do not set any client information (do not call `setClient`).


### InvoiceClient

-   `vat` — Client VAT number.
-   `name` — Client name.

> **Note:** Only required for non-final-consumer invoices.


### InvoiceItem

-   `reference` — Product reference.
-   `quantity` — Quantity of the item.
-   `setPrice(int $price): void` — Set price (in cents).
-   `setDescription(string $description): void` — Set item description.
-   `setTax(DocumentItemTax $tax): self` — Set tax type.
-   `setTaxExemption(TaxExemptionReason $reason): self` — Set tax exemption reason.
-   `setTaxExemptionLaw(string $law): self` — Set exemption law.
-   `setAmountDiscount(int $amount): self` — Set amount discount.
-   `setPercentageDiscount(int $percent): self` — Set percentage discount.

> **Note:** At least one item is required for every invoice.


### InvoicePayment

-   `method` — Payment method (see `DocumentPaymentMethod`).
-   `amount` — Payment amount (in cents).

> **Note:** At least one payment is required for every invoice.

---

For more details, see the source code in the `src/` directory.
