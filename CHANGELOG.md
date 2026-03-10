# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] — enhance-actions-structure

### Changed

- Refactored action classes (`InvoiceAction`, `ClientAction`, `ItemAction`) to use a shared `data` property via the base provider classes, removing the need to inject `*Data` into individual formatting methods.
- Contracts now extend one another to reduce boilerplate implementations across provider classes (`ShouldCreateInvoice`, `ShouldCreateClient`, `ShouldCreateItem`, etc.).
- Provider base classes (`Provider/Invoice`, `Provider/Client`, `Provider/Item`) made more abstract so logic can be reused across future providers without duplication.
- Dependency injection introduced in provider base classes to allow more abstract methods.
- Fixed `strict_types` enforcement across several provider files and action classes.

### Fixed

- PHPStan errors caused by missing PHPDoc blocks and incorrect type hints.
- `InvoiceAction` and `ItemAction` now correctly reflect the shared data property pattern.
- Class loading issue where a provider class was not autoloaded correctly.

---

## [Breaking Change] — January 2025

### Changed

- **Breaking:** Invoice creation entry point changed from the old standalone `Invoice` class to the `Invoice` facade backed by `InvoiceAction`.
  - Before: instantiated the `Invoice` class manually and called builder methods on it.
  - After: populate an `InvoiceData` DTO and pass it to `Invoice::create(InvoiceData $invoice)->execute()->getInvoice()`.
- `InvoiceData` now serves a dual role as both the input DTO and the hydrated response object returned by `getInvoice()`.
- All response fields (`id`, `sequence`, `total`, `totalNet`, `atcudHash`, `output`) are set on the returned `InvoiceData` instance; there is no separate response class.
- `OutputData` (accessible via `$result->output`) exposes `save()`, `fileName()`, and `content()` directly — no separate `Output` value object is needed.

[Unreleased]: https://github.com/csarcrr/invoicing-integration/compare/main...enhance-actions-structure
