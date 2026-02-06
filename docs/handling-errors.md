# Handling Errors

Things don't always go smoothly. The API key might be wrong, the provider might be down, or you might be missing a required field. This guide helps you understand what went wrong and how to fix it.

## Quick Reference

| Exception                         | Meaning         | First Thing to Check                 |
| --------------------------------- | --------------- | ------------------------------------ |
| `UnauthorizedException`           | Bad credentials | Your API key in `.env`               |
| `FailedReachingProviderException` | Provider down   | Internet connection, try again later |
| `RequestFailedException`          | Invalid data    | Check the error message for details  |

## Provider Exceptions

These exceptions occur when something goes wrong with the provider API.

### UnauthorizedException (401)

**What it means:** Your API credentials are invalid or missing.

**Common causes:**

- Incorrect `CEGID_VENDUS_API_KEY` in your `.env`
- API key has expired or been revoked
- Trailing spaces in the API key value
- Copy/paste error when setting up credentials

**How to fix:**

1. Double-check your API key in the Cegid Vendus dashboard
2. Ensure there are no extra spaces in your `.env` file
3. Verify the key matches the correct account/environment

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;

try {
    $result = $invoice->execute()->getInvoice();
} catch (UnauthorizedException $e) {
    Log::error('Invalid API credentials', [
        'message' => $e->getMessage(),
    ]);

    // Show user-friendly message
    return response()->json([
        'error' => 'Configuration error. Please contact support.',
    ], 500);
}
```

### FailedReachingProviderException (500)

**What it means:** The provider's API is unreachable or experiencing issues.

**Common causes:**

- Provider is having an outage
- Network connectivity issues on your server
- Firewall blocking outgoing HTTPS requests
- DNS resolution problems

**How to fix:**

1. Check if the provider's status page reports issues
2. Verify your server can reach external HTTPS endpoints
3. Try again in a few minutes (transient issue)
4. If persistent, check firewall rules

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;

try {
    $result = $invoice->execute()->getInvoice();
} catch (FailedReachingProviderException $e) {
    Log::warning('Provider temporarily unavailable', [
        'message' => $e->getMessage(),
    ]);

    // Queue for retry
    IssueInvoiceLater::dispatch($order)->delay(now()->addMinutes(5));

    return response()->json([
        'error' => 'Invoice will be issued shortly. We will email you when ready.',
    ]);
}
```

### RequestFailedException

**What it means:** The provider rejected your request due to invalid data or business rules.

**Common causes:**

- Missing required fields
- Duplicate invoice/client
- Invalid payment method ID
- Business rule violation (e.g., duplicate VAT)

**How to fix:** Read the error message carefully - it usually tells you exactly what's wrong.

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $result = $invoice->execute()->getInvoice();
} catch (RequestFailedException $e) {
    Log::error('Provider rejected request', [
        'message' => $e->getMessage(),
        'order_id' => $order->id,
    ]);

    // Parse error for user feedback
    return response()->json([
        'error' => 'Could not issue invoice: ' . $e->getMessage(),
    ], 422);
}
```

## Validation Exceptions

These exceptions are thrown **before** the request is sent to the provider, during local validation. This saves you from wasted API calls.

### Client Validation

| Exception                             | Meaning                        | How to Fix                                                         |
| ------------------------------------- | ------------------------------ | ------------------------------------------------------------------ |
| `InvoiceRequiresClientVatException`   | Client provided with empty VAT | Either provide a VAT number or don't set a client (final consumer) |
| `InvoiceRequiresVatWhenClientHasName` | Client has name but no VAT     | Add the client's VAT number                                        |

```php
// Wrong: empty VAT string
$client = ClientData::make([
    'name' => 'TechStore',
    'vat' => '',  // This will throw!
]);

// Right: either provide VAT or don't set client
$client = ClientData::make([
    'name' => 'TechStore',
    'vat' => 'PT509876543',
]);
// Or for final consumer, just don't call ->client()
```

### Credit Note Validation

| Exception                            | Meaning                            | How to Fix                         |
| ------------------------------------ | ---------------------------------- | ---------------------------------- |
| `CreditNoteReasonIsMissingException` | NC type without reason             | Call `->creditNoteReason('...')`   |
| `MissingRelatedDocumentException`    | Credit note item without reference | Add `relatedDocument` to each item |

```php
// Wrong: missing reason
$invoice->type(InvoiceType::CreditNote);
$invoice->execute()->getInvoice();  // Throws CreditNoteReasonIsMissingException

// Right: provide reason
$invoice->type(InvoiceType::CreditNote);
$invoice->creditNoteReason('Customer return - item defective');
$invoice->execute()->getInvoice();
```

### Item Validation

| Exception                                         | Meaning                           | How to Fix                     |
| ------------------------------------------------- | --------------------------------- | ------------------------------ |
| `UnsupportedQuantityException`                    | Quantity is zero or negative      | Use a positive quantity        |
| `ExemptionCanOnlyBeUsedWithExemptTaxException`    | Tax exemption without EXEMPT rate | Set `tax` to `ItemTax::EXEMPT` |
| `ExemptionLawCanOnlyBeUsedWithExemptionException` | Exemption law without reason      | Set `taxExemptionReason` first |

```php
// Wrong: tax exemption on normal-rated item
$item = ItemData::make([
    'reference' => 'PRODUCT',
    'price' => 1000,
    'tax' => ItemTax::NORMAL,
    'taxExemptionReason' => TaxExemptionReason::M04,  // Throws!
]);

// Right: use EXEMPT tax rate
$item = ItemData::make([
    'reference' => 'PRODUCT',
    'price' => 1000,
    'tax' => ItemTax::EXEMPT,
    'taxExemptionReason' => TaxExemptionReason::M04,
    'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
]);
```

### Transport Validation

| Exception                          | Meaning                       | How to Fix                           |
| ---------------------------------- | ----------------------------- | ------------------------------------ |
| `NeedsDateToSetLoadPointException` | Transport without origin date | Set the origin date                  |
| `InvalidCountryException`          | Invalid ISO country code      | Use valid 2-letter code (PT, ES, FR) |

## Catching Multiple Exceptions

Handle different errors appropriately in production:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;

try {
    $result = $invoice->execute()->getInvoice();

    // Success - save the result
    $order->invoice_sequence = $result->sequence;
    $order->save();

} catch (UnauthorizedException $e) {
    // Configuration problem - alert developers
    Log::critical('API credentials invalid', ['error' => $e->getMessage()]);
    throw $e; // Let it bubble up

} catch (FailedReachingProviderException $e) {
    // Temporary issue - retry later
    Log::warning('Provider unavailable, queuing retry');
    RetryInvoice::dispatch($order)->delay(now()->addMinutes(10));

} catch (RequestFailedException $e) {
    // Data problem - log and notify user
    Log::error('Invoice rejected', [
        'order_id' => $order->id,
        'error' => $e->getMessage(),
    ]);

    return back()->withErrors([
        'invoice' => 'Could not issue invoice: ' . $e->getMessage(),
    ]);
}
```

## Debugging Tips

1. **Check the full error message** - Provider errors usually include specific details
2. **Enable Laravel debug mode** temporarily to see stack traces
3. **Use Laravel Ray or Telescope** to inspect the actual API request/response
4. **Test in sandbox mode first** - Set `CEGID_VENDUS_MODE=tests` during development

---

For a complete list of exceptions, see [API Reference - Exceptions](api-reference.md#exceptions).
