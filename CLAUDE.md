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
- **`src/Enums/`** — Strongly-typed domain enums (`InvoiceType`, `ItemTax`, `TaxExemptionReason`, `PaymentMethod`, `Provider`)
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

### Namespaces

| Path | Namespace |
|---|---|
| `src/` | `CsarCrr\InvoicingIntegration\` |
| `tests/` | `CsarCrr\InvoicingIntegration\Tests\` |
| `database/factories/` | `CsarCrr\InvoicingIntegration\Database\Factories\` |
| `workbench/app/` | `Workbench\App\` |

### Service Provider

`CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider`
