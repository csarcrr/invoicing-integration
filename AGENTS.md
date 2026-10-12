# Project-Specific Rules

## Project Context

- This is a Laravel framework package (`csarcrr/invoicing-integration`) providing a provider-agnostic API for Portuguese fiscal invoicing, currently supporting Cegid Vendus
- Uses Pest PHP for testing
- Uses PHPStan (Larastan) for static analysis
- Uses Pint for code formatting/linting

## Commands

```bash
composer test        # Run Pest tests in parallel
composer analyse     # Run PHPStan static analysis (level 7)
composer format      # Run Laravel Pint code formatter
composer complete    # Run all: format + analyse + test
```

To run a single test file:

```bash
./vendor/bin/pest tests/Unit/Invoice/CreateTest.php
```

## PHP Requirements

- **Minimum PHP version: 8.2**
- Always include `declare(strict_types=1);` at the top of all PHP files
- Follow PHP 8.2+ features and best practices

## Laravel Compatibility

- Supports Laravel 11.x and 12.x (`illuminate/contracts: ^11.0||^12.0`)
- Uses `spatie/laravel-package-tools` for package scaffolding
- Test against multiple Laravel versions using Orchestra Testbench

## Architecture

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

### DTOs

All domain data uses `Spatie\LaravelData`. DTOs validate on construction. Use `Optional` (from `src/Helpers/Properties.php`) when a field may be intentionally absent vs. `null`.

## Namespace Conventions

| Path                  | Namespace                                          |
| --------------------- | -------------------------------------------------- |
| `src/`                | `CsarCrr\InvoicingIntegration\`                    |
| `tests/`              | `CsarCrr\InvoicingIntegration\Tests\`              |
| `database/factories/` | `CsarCrr\InvoicingIntegration\Database\Factories\` |
| `workbench/app/`      | `Workbench\App\`                                   |

## Key Dependencies

- `league/iso3166` (^4.3) - ISO 3166 country codes library
- `spatie/laravel-package-tools` (^1.16) - Laravel package development utilities

## Dev Dependencies

- `larastan/larastan` (^3.0) - PHPStan for Laravel
- `laravel/pint` (^1.14) - Code formatting
- `pestphp/pest` (^3.0) - Testing framework
- `pestphp/pest-plugin-arch` (^3.0) - Architecture testing
- `pestphp/pest-plugin-laravel` (^3.0) - Laravel-specific Pest helpers
- `orchestra/testbench` (^10.3.0||^9.3.0) - Laravel package testing
- `spatie/laravel-ray` (^1.35) - Debugging tool

## Testing

- Write tests using Pest PHP syntax — `it()`, `test()`, `expect()` — never raw PHPUnit
- Use `pestphp/pest-plugin-arch` for architecture tests
- Tests mirror `src/` structure under `tests/Unit/`
- Run tests: `composer test`
- Run tests with coverage: `composer test-coverage`

## Code Quality

- Run PHPStan for static analysis before committing: `composer analyse`
- Run Pint for code formatting: `composer format`
- Follow Laravel package development conventions
- PHPStan uses deprecation rules and PHPUnit extensions

## Composer Scripts

- `composer test` - Run Pest tests
- `composer test-coverage` - Run tests with coverage report
- `composer analyse` - Run PHPStan static analysis
- `composer format` - Run Pint code formatter

## Documentation

- Never suggest what the user should or should not do from a legal standpoint in any documentation

## Service Provider & Facade

- Service Provider: `CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider`
- Facade: `CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration`
- Alias: `InvoicingIntegration`
