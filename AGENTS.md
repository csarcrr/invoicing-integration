# AGENTS.md

Rules for every coding agent working in this repository.

## 1. Project

- `csarcrr/invoicing-integration` is a Laravel package with a provider-agnostic API for Portuguese fiscal invoicing.
- Providers: Vendus and Moloni.
- `GLOSSARY.md` defines the domain terms and the name each provider uses for them. Use its terms in code, tests and docs.

## 2. Stack

- PHP 8.2+, Laravel 11 to 13, Spatie Laravel Data, Pest, PHPStan level 7, Pint.
- Exact versions live in `composer.json`. Read it instead of guessing.

## 3. Commands

```bash
composer test        # Pest tests in parallel
composer test:group  # Pest tests by group
composer analyse     # PHPStan (level 7)
composer format      # Pint code formatter
composer complete    # format, analyse and test
```

- Never run test, lint, format or analysis commands: no `composer test`, `composer analyse`, `composer format`, `composer complete`, `pest`, `phpstan` or `pint`.
- The user runs them. Your job is to solve the problem with code, not to verify it.

## 4. Architecture

### Request flow

User calls a Facade → Action class → Provider implementation → HTTP → Provider API

```
Facades/Invoice::create(InvoiceData)
  → Actions/InvoiceAction (match on Provider enum)
    → Provider/Vendus/Invoice/Create
      → buildPayload() assembles request
        → Http::provider() macro (configured HTTP client)
```

### Key directories

- `src/Actions/`: orchestrators that route each operation to the provider via `match` on the `Provider` enum.
- `src/Contracts/IntegrationProvider/<Resource>/`: one contract per operation, named `Should<Operation><Resource>` (e.g. `ShouldCreateInvoice`, `ShouldCreateClient`).
- `src/Data/`: Spatie Laravel Data DTOs, validated on construction.
- `src/Enums/`: domain enums. `Tax/` holds `ItemTax`; `Providers/` holds the supported-property enums.
- `src/Facades/`: one facade per resource (`Invoice`, `Client`, `Item`, ...).
- `src/Provider/Vendus/` and `src/Provider/Moloni/`: provider-specific logic, mirroring the resource sub-folders.
- `src/Exceptions/`: domain exceptions grouped by concern.
- `src/Traits/`: shared traits such as `HasMakeValidation`, `HasPaginator`, `HasConfig`, `EnumOptions`.
- `src/Configuration/HttpConfiguration.php`: provider HTTP configuration.
- `config/invoicing-integration.php`: provider configuration.

### Adding a provider

1. Add a case to `src/Enums/Provider.php`.
2. Add the provider config to `config/invoicing-integration.php`.
3. Implement the relevant contracts in `src/Provider/<ProviderName>/`.
4. Add the provider case to the `match` in each Action class.

### HTTP layer

Two macros are registered in the service provider:

- `Http::provider()`: returns a pre-configured HTTP client for the active provider.
- `Http::handleUnwantedFailures()`: maps HTTP status codes to domain exceptions.

### Namespaces

| Path | Namespace |
| --- | --- |
| `src/` | `CsarCrr\InvoicingIntegration\` |
| `tests/` | `CsarCrr\InvoicingIntegration\Tests\` |

### Service provider

`CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider`

## 5. Provider API docs

- Offline copies of the provider API docs live in `providers-docs/`. Read them first for any endpoint, parameter or response lookup. Go to the web only when they lack what you need.

| Provider | Local docs |
| --- | --- |
| Moloni | `providers-docs/moloni/` (HTML pages, e.g. `providers-docs/moloni/dev/settings/payment-methods/insert/index.html`) |
| Vendus | `providers-docs/vendus/` (`openapi.json` is the full API spec; `index.html` is its Redoc viewer) |

- The package calls Vendus API v1.2 (`src/Configuration/HttpConfiguration.php`). The local `openapi.json` is the v1.2 spec and the source of truth for Vendus endpoints and field names.
- Vendus v1.2 nests product fields (`prices.gross`, `tax.id`, `stock.control`). Document line items keep flat names (`gross_price`, `tax_id`).

## 6. Domain decisions

- An item carries exactly one tax. Do not design for multiple taxes per item.
- On Moloni items, `ItemTax::EXEMPT` maps to a configured 0% tax sent with `exemption_reason`, never to an empty `taxes` list.
- Changing only an item's tax keeps its gross price; the net price is recalculated.

## 7. Working rules

- **Think before coding.** State assumptions. If uncertain, ask before implementing. If a request has several interpretations, present them. If a simpler approach exists, say so. If something is unclear, stop, name what is confusing, and ask.
- **Provider operation plans.** List each DTO field, the provider field it maps to, and the provider docs reference. State each open mapping decision as a question.
- **Simplicity first.** Write the minimum code that solves the problem. Add no features, config options or flexibility that were not requested. No abstractions for single-use code. No error handling for impossible scenarios; validate at the boundaries (DTOs, provider responses). If code could be done in a quarter of the lines, rewrite it.
- **Surgical changes.** Every changed line traces to the request. Do not reformat, restyle or refactor adjacent code that is not broken. Match the style of the surrounding code. Delete dead code you find and list what you removed. Remove imports, variables and methods that your change made unused.
- **TDD.** Work test-first, without stopping between steps: write the Pest tests that describe the expected behaviour, then the minimum implementation that passes them. Refactor only while the tests' expected behaviour stays intact.
- **Provider rules belong to the provider.** Do not validate, default or work around a provider's own requirement (a required field, a document needing a client address, a payment that must match the total). Do not report it as a package bug. Send the request as built and let the provider's error reach the developer.
- **Map, do not manage.** Every field the developer sets must reach the provider in the shape the provider expects. Fix only cases where the package drops, renames or mis-shapes data, or where no input can produce a field the provider needs.
- **Provider notes.** Provider rules a developer is likely to hit go in that provider's section of the docs as a note. The note informs; the code stays unchanged.
- **Dependencies.** Before relying on how a dependency behaves (Spatie Laravel Data casting, validation, rule inference, any other package), read its source in `vendor/`. Base the code on the source, not on assumptions.
- **Nested DTOs.** Pass nested DTOs and collections to `make()` as arrays. Spatie builds them from the property type or its `@var` docblock; a `Collection` or `Data` instance fails the inferred `array` rule.
- **Docs sync.** Docs under `docs/` must match the code at all times. A change that alters behaviour, signatures, supported providers or supported operations updates the affected docs pages in the same change. List the pages you updated in your summary; if none needed an update, say so.
- **Task completion.** Never declare a task done. Hand the work back and let the user judge completion.

## 8. Code style

- `declare(strict_types=1);` at the top of every PHP file. Type-hint every parameter and return type.
- PSR-12.
- No comments. Type-only docblocks are allowed when the language cannot express the type (generics, array shapes, `@var`, `@return Collection<...>`, `@method`, `@throws`, `@template`). No prose docblocks, even when the surrounding code has them.
- Use `match` over `switch`. Use early returns; no `else` after a return.
- Use `===`, never `==`.
- Use `readonly` where appropriate.
- Use enums instead of class constants for fixed sets of values.
- English everywhere: code, UI text, commit messages, docs, replies.
- Existing `pt` translation files stay in Portuguese. Edit them in European Portuguese (PT-PT), never Brazilian.

## 9. Git and PRs

- Never commit unless the user asks.
- Commit messages: one sentence, imperative mood, with a `feat:`, `fix:`, `docs:` or `chore:` prefix (`feat!` or `fix!` for breaking changes). State what changed and why. No body.
- PR descriptions have no checkboxes.

## 10. File-scoped rules

Before editing a file that matches a glob, read the rules file for it.

| Editing | Read first |
| --- | --- |
| `**/*.php` | `.agents/rules/php-code.md` |
| `src/**/*.php` | `.agents/rules/solid.md` |
| `src/Data/**` | `.agents/rules/dtos.md` |
| `src/Provider/**`, `src/Contracts/**`, `src/Actions/**`, `src/Facades/**` | `.agents/rules/provider-operations.md` |
| `tests/**` | `.agents/rules/testing.md` |

## 11. Documentation

- Never suggest what the user should or should not do from a legal standpoint in any documentation.
