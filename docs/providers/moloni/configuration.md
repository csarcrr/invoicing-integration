# Providers - Moloni - Configuration

> [!NOTE]
> Moloni support is actively being built. Authentication, client management (create, get, find), item create and get, and invoice creation are implemented; other features are coming soon. Check the [Features](/features.md) page to track implementation progress.

## Authentication

This package uses Moloni's **password grant** (they call it "Native Application") authentication method. This is a deliberate choice to keep the integration as seamless as possible for developers using the package.

### Why password grant?

Moloni's API offers two authentication methods:

| Method                 | Grant Type           | How it works                                                                                                                                                    |
| ---------------------- | -------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Web Application**    | `authorization_code` | Requires the end-user to manually visit Moloni's login page, authorize the app, and then a `code` is redirected back — which must then be exchanged for tokens. |
| **Native Application** | `password`           | Sends developer credentials + user credentials directly to the token endpoint. Tokens are returned immediately.                                                 |

The Web Application method is more secure (credentials don't pass through third-party interfaces), but it requires a multi-step manual OAuth flow that is difficult to abstract behind a provider-agnostic API like this package.

We opted for the **password grant** because:

- **Simpler setup** — developers only need to provide their Moloni credentials as environment variables
- **No redirect flow** — no callback URLs, no manual code exchange, no browser redirects
- **Consistent DX** — configuring Moloni feels the same as configuring any other provider in this package
- **The package handles the rest** — the access token is requested, cached and renewed entirely behind the scenes

### Obtaining Your Credentials

You never request or store a token yourself. The package only needs the values below.

#### Developer ID and Client Secret

1. Register a Moloni account if you do not have one. Developing against the API does not require a paid plan.
2. In Moloni, open **Configurações → Developers → Configuração de conta e API** and tick **Ativar API**.
3. Fill in **Developer ID** (Moloni suggests your account slug) and **URI de Resposta (Callback)**. The callback is mandatory in the form but is not used by the password grant, so any URL you control works.
4. Click **Atualizar**. Moloni generates the **Chave Secreta**: this is your Client Secret.

Use the Developer ID as `MOLONI_DEVELOPER_ID` and the Chave Secreta as `MOLONI_CLIENT_SECRET`.

#### Username and Password

The login of the Moloni user whose company you want to operate on: the same email and password used at [moloni.pt](https://www.moloni.pt/). Use them as `MOLONI_USERNAME` and `MOLONI_PASSWORD`.

#### Company ID

Moloni does not show the company ID alongside the API settings. Ask the API for the companies your user can access:

```bash
curl -G "https://api.moloni.pt/v1/grant/" \
  --data-urlencode "grant_type=password" \
  --data-urlencode "client_id=$MOLONI_DEVELOPER_ID" \
  --data-urlencode "client_secret=$MOLONI_CLIENT_SECRET" \
  --data-urlencode "username=$MOLONI_USERNAME" \
  --data-urlencode "password=$MOLONI_PASSWORD"
```

The response contains an `access_token` valid for 1 hour. Use it to list the companies:

```bash
curl -X POST "https://api.moloni.pt/v1/companies/getAll/?access_token=<access_token>"
```

Each entry has a `company_id`. Use the one you want as `MOLONI_COMPANY_ID`.

The same `access_token` works for looking up the other IDs this page asks for (customers, measurement units, taxes, payment methods) through the matching Moloni endpoints, sending `company_id` in the POST body.

### Environment Variables

Add the following to your `.env` file:

```bash
# Moloni credentials
MOLONI_DEVELOPER_ID=your-developer-id
MOLONI_CLIENT_SECRET=your-client-secret
MOLONI_USERNAME=your-email@example.com
MOLONI_PASSWORD=your-password
MOLONI_COMPANY_ID=your-company-id
MOLONI_NO_VAT_CLIENT_ID=your-no-vat-customer-id

# Measurement unit IDs (items)
MOLONI_UNIT_KG_ID=
MOLONI_UNIT_UNIT_ID=

# Tax IDs (items)
MOLONI_TAX_NORMAL_ID=
MOLONI_TAX_INTERMEDIATE_ID=
MOLONI_TAX_REDUCED_ID=
MOLONI_TAX_OTHER_ID=
MOLONI_TAX_OTHER_RATE=0

# Payment method IDs (invoices)
MOLONI_PAYMENT_MB_ID=
MOLONI_PAYMENT_CREDIT_CARD_ID=
MOLONI_PAYMENT_CURRENT_ACCOUNT_ID=
MOLONI_PAYMENT_MONEY_ID=
MOLONI_PAYMENT_MONEY_TRANSFER_ID=
```

#### `MOLONI_DEVELOPER_ID`

Your Moloni Developer ID (see [Obtaining Your Credentials](#obtaining-your-credentials)). This identifies your application when requesting access tokens.

#### `MOLONI_CLIENT_SECRET`

Your Moloni Client Secret, also available in the client area. Used alongside the Developer ID to authenticate your application with the Moloni API.

#### `MOLONI_USERNAME`

Your Moloni account username — usually your email address.

#### `MOLONI_PASSWORD`

Your Moloni account password.

#### `MOLONI_COMPANY_ID`

The ID of the Moloni company to operate on. It is sent automatically with every request.

#### `MOLONI_NO_VAT_CLIENT_ID`

The ID of the Moloni customer used on invoices that have no client, or whose client has no `id`. When it is not set, `customer_id` is sent as `0` and Moloni rejects the document.

#### `MOLONI_UNIT_*_ID`

Moloni measurement unit IDs, mapped to the values of the `Unit` enum (or any custom enum implementing `ShouldBeUnit`). Required when creating items with a `unit`; a missing mapping throws `CouldNotGetUnitIdException`.

#### `MOLONI_TAX_*_ID`

Moloni tax IDs, mapped to the values of the `ItemTax` enum. Each entry also holds the tax rate, which is used to convert the gross `price` of an item into the net price Moloni expects. Exempt items (`ItemTax::EXEMPT`) send no taxes and use `taxExemptionReason` instead. A missing mapping throws `CouldNotGetTaxIdException`.

#### `MOLONI_PAYMENT_*_ID`

Moloni payment method IDs, mapped to the values of the `PaymentMethod` enum. Required when an invoice has payments; a missing mapping throws an `Exception` with the message `Payment method not configured.`.

### Token Management

You don't need to handle token lifecycles manually. The package manages Moloni tokens automatically:

- **Access token** — cached for its full validity period (1 hour), with a small buffer subtracted to avoid edge-case expirations
- On each request, the package checks the cache first. If a valid token is found, it's reused without making a new HTTP request to Moloni's token endpoint
- If the cache is empty or expired, a fresh token is requested automatically with the password grant. Moloni's refresh token is not used
- If Moloni rejects the credentials, a `RequestFailedException` is thrown with Moloni's error, for example `invalid_grant: ...`

### Configuration File

The published configuration file (`config/invoicing-integration.php`) includes a `Moloni` section:

```php
'Moloni' => [
    'developer_id' => env('MOLONI_DEVELOPER_ID', null),
    'client_secret' => env('MOLONI_CLIENT_SECRET', null),
    'username' => env('MOLONI_USERNAME', null),
    'password' => env('MOLONI_PASSWORD', null),
    'company_id' => env('MOLONI_COMPANY_ID', null),
    'no_vat_client_id' => env('MOLONI_NO_VAT_CLIENT_ID', null),
    'payments' => [
        PaymentMethod::MB->value => env('MOLONI_PAYMENT_MB_ID', null),
        PaymentMethod::CREDIT_CARD->value => env('MOLONI_PAYMENT_CREDIT_CARD_ID', null),
        PaymentMethod::CURRENT_ACCOUNT->value => env('MOLONI_PAYMENT_CURRENT_ACCOUNT_ID', null),
        PaymentMethod::MONEY->value => env('MOLONI_PAYMENT_MONEY_ID', null),
        PaymentMethod::MONEY_TRANSFER->value => env('MOLONI_PAYMENT_MONEY_TRANSFER_ID', null),
    ],
    'units' => [
        'kg' => env('MOLONI_UNIT_KG_ID', null),
        'unit' => env('MOLONI_UNIT_UNIT_ID', null),
    ],
    'taxes' => [
        ItemTax::NORMAL->value => ['id' => env('MOLONI_TAX_NORMAL_ID', null), 'rate' => 23],
        ItemTax::INTERMEDIATE->value => ['id' => env('MOLONI_TAX_INTERMEDIATE_ID', null), 'rate' => 13],
        ItemTax::REDUCED->value => ['id' => env('MOLONI_TAX_REDUCED_ID', null), 'rate' => 6],
        ItemTax::OTHER->value => ['id' => env('MOLONI_TAX_OTHER_ID', null), 'rate' => env('MOLONI_TAX_OTHER_RATE', 0)],
    ],
],
```

Items created in Moloni also require a `category` (`CategoryData` with an `id`); omitting it throws `MissingCategoryException`.

No additional configuration is needed beyond setting the environment variables.

## Next Steps

Moloni features follow the same patterns as other providers. See:

- [Creating an Invoice](/invoices/creating-an-invoice.md)
- [Managing Clients](/clients/README.md)
- [Managing Items](/items/README.md)
