# Handling Errors

When interacting with invoicing providers, various errors can occur. This package provides specific exceptions to help you handle these scenarios appropriately.

## Provider Exceptions

These exceptions are thrown when communication with the provider fails or returns an error.

### UnauthorizedException

Thrown when the API credentials are invalid or missing.

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
```

This typically occurs when:

- The API key is incorrect
- The API key has expired
- The API key is missing from configuration

### FailedReachingProviderException

Thrown when the provider cannot be reached or returns a server error (HTTP 500).

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
```

This typically occurs when:

- The provider's API is down
- Network connectivity issues
- The provider is experiencing internal errors

### RequestFailedException

Thrown when the provider returns a validation or business logic error.

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
```

This typically occurs when:

- Invalid data was sent to the provider
- Business rules were violated (e.g., duplicate invoice)
- Required fields are missing according to the provider

## Validation Exceptions

These exceptions are thrown before the request is sent to the provider, during local validation.

| Exception                                         | When Thrown                                 |
| ------------------------------------------------- | ------------------------------------------- |
| `InvoiceRequiresClientVatException`               | Client provided with empty VAT              |
| `InvoiceRequiresVatWhenClientHasName`             | Client has name but no VAT                  |
| `CreditNoteReasonIsMissingException`              | NC type without credit note reason          |
| `NeedsDateToSetLoadPointException`                | Transport without origin date               |
| `InvalidCountryException`                         | Invalid ISO country code                    |
| `UnsupportedQuantityException`                    | Item quantity is zero or negative           |
| `MissingRelatedDocumentException`                 | Credit note item without related document   |
| `ExemptionCanOnlyBeUsedWithExemptTaxException`    | Tax exemption set without `ItemTax::EXEMPT` |
| `ExemptionLawCanOnlyBeUsedWithExemptionException` | Exemption law set without exemption reason  |

## Output Exceptions

| Exception                       | When Thrown                                     |
| ------------------------------- | ----------------------------------------------- |
| `InvoiceWithoutOutputException` | Calling `getOutput()` when no output is present |

---

For a complete list of exceptions, see [API Reference](api-reference.md#exceptions).
