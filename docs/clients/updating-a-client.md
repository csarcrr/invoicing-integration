# Updating a Client

Change the details of a stored client using `Client::update()`.

## Basic Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

$clientData = ClientData::make([
    'id' => '12345',
    'reference' => 'CLIENT-1',
    'name' => 'TechStore Portugal Lda',
    'vat' => '509876543',
    'address' => 'Zona Industrial do Porto, Lote 15',
    'city' => 'Porto',
    'email' => 'invoices@techstore.pt',
]);

$client = Client::update($clientData)->execute()->getClient();
```

> [!NOTE]
> On Moloni, `getClient()` returns the `ClientData` you passed in, because Moloni does not return the client. The Moloni `valid` and `customer_id` values are in `getAdditionalData()`. On Cegid Vendus, `getClient()` returns the client as stored after the update.

## Requirements

The `id` property is **required** when updating a client. It is the provider client id: the Moloni `customer_id` or the Cegid Vendus client id, the one `Client::get()` expects.

```php
// This will throw an InvalidArgumentException
$clientData = ClientData::make(['name' => 'TechStore Portugal Lda']);
Client::update($clientData)->execute(); // Error: Client ID is required.

// Correct usage
$clientData = ClientData::make(['id' => '12345', 'name' => 'TechStore Portugal Lda']);
Client::update($clientData)->execute(); // Works
```

## Updated Fields

| Field        | Moloni                                              | Cegid Vendus                |
| ------------ | --------------------------------------------------- | --------------------------- |
| `id`         | `customer_id` (required)                            | Path `/clients/{id}`        |
| `reference`  | `number`                                            | `external_reference`        |
| `vat`        | `vat`                                               | `fiscal_id`                 |
| `name`       | `name`                                              | `name`                      |
| `email`      | `email`                                             | `email`                     |
| `phone`      | `phone`                                             | `phone`                     |
| `address`    | `address`                                           | `address`                   |
| `city`       | `city`                                              | `city`                      |
| `postalCode` | `zip_code`                                          | `postalcode`                |
| `country`    | `country_id` (looked up from the ISO code, not sent when unset) | `country` (ISO code)         |
| `notes`      | `notes`                                             | `notes`                     |
| `defaultPayDue` | `maturity_date_id`                               | `default_pay_due`           |
| `status`     | not sent                                            | `status` (`active` or `inactive`) |

The other fields behave as they do in `Client::create()` on each provider. See [Creating a Client](creating-a-client.md#moloni) for the details.

### Fields You Do Not Set

On Cegid Vendus, fields you do not set are not sent, so the provider keeps their stored values.

On Moloni, the update sends only the fields you set, as `Client::create()` does. Fields you leave unset are not sent.

## Moloni

- `name`, `vat`, `address` and `city` are required, as on create. A missing one throws `MissingClientDataException` before the request.
- `reference` is sent as the Moloni customer `number`. Moloni requires it, and the package does not generate one. Without `reference`, no `number` is sent.
- Moloni ignores changes to `vat` and `number` once a document references the client.
- The response has no client body. `getClient()` returns the `ClientData` you passed in, with the `valid` and `customer_id` values in `getAdditionalData()`.

## Cegid Vendus

- `status` is sent only when you set it.
- `getClient()` returns the stored client, the same as `Client::get()`.

## Error Handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;

try {
    $client = Client::update($clientData)->execute()->getClient();
} catch (UnauthorizedException $e) {
    // Invalid API credentials - check your .env
    Log::error('Invalid API credentials', ['error' => $e->getMessage()]);
} catch (RequestFailedException $e) {
    // Provider rejected the update
    Log::error('Client update failed', ['error' => $e->getMessage()]);
    // Common causes: VAT already used by another client, invalid data, missing required fields
}
```

---

Keep the provider-assigned `id` with your customer record, so you can update the client later. Use `Client::get()` to read the stored client before you change it.

---

Back to: [Clients Overview](README.md)
