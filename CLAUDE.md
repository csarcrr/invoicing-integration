# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture

This is a **Laravel package** (`csarcrr/invoicing-integration`) providing a provider-agnostic API for Portuguese fiscal invoicing, currently supporting Cegid Vendus.

### Request Flow

User calls a Facade → Action class → Provider implementation → HTTP → Provider API

```
Facades/Invoice::create(InvoiceData)
  → Actions/InvoiceAction (match on Provider enum)
    → Provider/CegidVendus/Invoice/Create
      → buildPayload() assembles request
        → Http::provider() macro (configured HTTP client)
```

### Key Directories

- **`src/Actions/`** — Orchestrators that route operations to the correct provider implementation via `match` on `Provider` enum
- **`src/Contracts/`** — Interfaces that all provider implementations must satisfy (e.g., `ShouldCreateInvoice`, `CreateClient`)
- **`src/Data/`** — Spatie Laravel Data DTOs (`InvoiceData`, `ItemData`, `ClientData`, etc.) — validated on instantiation
- **`src/Enums/`** — Strongly-typed domain enums (`InvoiceType`, `ItemTax`, `PaymentMethod`, `Provider`)
- **`src/Provider/CegidVendus/`** — All Cegid Vendus-specific logic, mirroring the `Invoice/`, `Client/`, `Item/` sub-structure
- **`src/Exceptions/`** — Domain exceptions organized by concern (`Providers/`, `Invoice/`, `Pagination/`)
- **`src/Traits/`** — `HasMakeValidation`, `HasPaginator`, `HasConfig`, `EnumOptions`

### Adding a New Provider

1. Add a case to `Enums/Provider`
2. Add provider config to `config/invoicing-integration.php`
3. Implement the relevant contracts (e.g., `ShouldCreateInvoice`) in `src/Provider/<ProviderName>/`
4. Add the provider case to the `match` in each `Action` class

### HTTP Layer

Two custom HTTP macros registered in the service provider:
- `Http::provider()` — Returns a pre-configured HTTP client for the active provider
- `Http::handleUnwantedFailures()` — Centralized mapping of HTTP status codes to domain exceptions

### Provider API Docs

Offline copies of the provider API docs live in `providers-docs/`. For any endpoint, parameter or response lookup, read the local copy first and go to the web only when the local docs lack what is needed.

| Provider | Local docs |
|---|---|
| Moloni | `providers-docs/moloni/` (HTML pages, e.g. `providers-docs/moloni/dev/settings/payment-methods/insert/index.html`) |
| Cegid Vendus | `providers-docs/vendus/` (`openapi.json` is the full API spec; `index.html` is its Redoc viewer) |

The package calls Cegid Vendus API v1.2 (`src/Configuration/HttpConfiguration.php`), and the local `openapi.json` is the v1.2 spec: it is the source of truth for Vendus endpoints and field names. v1.2 nests product fields (`prices.gross`, `tax.id`, `stock.control`); document line items keep flat names (`gross_price`, `tax_id`).

### Domain Decisions

- An item carries exactly one tax. Don't design for multiple taxes per item.
- On Moloni items, `ItemTax::EXEMPT` maps to a configured 0% tax sent with `exemption_reason`, never an empty `taxes` list.
- Changing only an item's tax keeps its gross price; the net price is recalculated.

### Namespaces

| Path | Namespace |
|---|---|
| `src/` | `CsarCrr\InvoicingIntegration\` |
| `tests/` | `CsarCrr\InvoicingIntegration\Tests\` |
| `database/factories/` | `CsarCrr\InvoicingIntegration\Database\Factories\` |
| `workbench/app/` | `Workbench\App\` |

### Service Provider

`CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider`
