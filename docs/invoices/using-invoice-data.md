
# Using the InvoiceData Class

The `InvoiceData` class is returned by all providers when issuing an invoice. It unifies the response format, so you can always access invoice details (such as the provider's reference or internal ID) in the same way, regardless of which integration you use. This makes your code simpler and provider-agnostic.

## Example Usage

When you issue an invoice, you receive an `InvoiceData` object:

```php
$invoiceData = $invoice->execute();

// Get the provider's invoice reference
$reference = $invoiceData->sequence(); // FT 01P2025/1

// Get the internal ID of the provider (when set)
$id = $invoiceData->id();
```

---

**Note:**
Always use the `InvoiceData` methods to access invoice details, so your code works with any provider and remains future-proof.
