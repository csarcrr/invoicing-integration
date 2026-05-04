# Providers - Moloni - Configuration

> [!NOTE]
> Moloni support is actively being built. Authentication is currently implemented; other features (invoices, items, clients) are coming soon. Check the [Features](/features.md) page to track implementation progress.

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
- **The package handles the rest** — token lifecycle (access + refresh) is managed entirely behind the scenes

### Environment Variables

Add the following to your `.env` file:

```bash
# Moloni credentials
MOLONI_DEVELOPER_ID=your-developer-id
MOLONI_CLIENT_SECRET=your-client-secret
MOLONI_USERNAME=your-email@example.com
MOLONI_PASSWORD=your-password
```

#### `MOLONI_DEVELOPER_ID`

Your Moloni Developer ID, available in the [Moloni client area](https://www.moloni.pt/). This identifies your application when requesting access tokens.

#### `MOLONI_CLIENT_SECRET`

Your Moloni Client Secret, also available in the client area. Used alongside the Developer ID to authenticate your application with the Moloni API.

#### `MOLONI_USERNAME`

Your Moloni account username — usually your email address.

#### `MOLONI_PASSWORD`

Your Moloni account password.

### Token Management

You don't need to handle token lifecycles manually. The package manages Moloni tokens automatically:

- **Access token** — cached for its full validity period (1 hour), with a small buffer subtracted to avoid edge-case expirations
- **Refresh token** — cached alongside the access token (valid for 14 days)
- On each request, the package checks the cache first. If a valid token is found, it's reused without making a new HTTP request to Moloni's token endpoint
- If the cache is empty or expired, a fresh token is requested automatically

### Configuration File

The published configuration file (`config/invoicing-integration.php`) includes a `Moloni` section:

```php
'Moloni' => [
    'developer_id' => env('MOLONI_DEVELOPER_ID', null),
    'client_secret' => env('MOLONI_CLIENT_SECRET', null),
    'username' => env('MOLONI_USERNAME', null),
    'password' => env('MOLONI_PASSWORD', null),
],
```

No additional configuration is needed beyond setting the environment variables.

## Next Steps

Once Moloni features (invoices, items, clients) are implemented, using them will follow the same patterns as other providers. See:

- [Creating an Invoice](/invoices/creating-an-invoice.md)
- [Managing Clients](/clients/README.md)
- [Managing Items](/items/README.md)
