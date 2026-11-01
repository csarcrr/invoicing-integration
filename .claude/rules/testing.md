---
paths:
  - "tests/**"
---

# Testing Conventions

- Write tests using Pest syntax — `it()`, `test()`, `expect()` — never raw PHPUnit
- Use `pestphp/pest-plugin-arch` for architecture tests
- Organize tests by operation under `tests/Unit/<Resource>/<Operation>/` and `tests/Feature/<Resource>/`, not by `src/` structure. An operation with many payload fields splits them into `ProviderData/`; the others keep a single `PayloadTest.php`
- Provider internals with no counterpart in other providers go in `tests/Unit/Providers/<Provider>/`
- Before writing a test, read a sibling test for the same kind of operation and follow its structure
- Fake provider HTTP with `Http::fake(mockResponse(fixtures()->...))` and no URL key; `fixtures()` already resolves the active provider
- Put per-provider differences in a `match ($provider)` inside the assertion (e.g. inside `Http::assertSent`), including when providers behave differently, instead of one copied test per provider
- Give both providers' fixtures the same values where the provider format allows it, so the expected values stay shared
- When only some providers support an operation, its tests call `mockConfiguration(Provider::X)` in `beforeEach` and do not use the `providers` dataset. The structure test runs on the `providers` dataset and is the one place that asserts `OperationNotSupportedException`
- When a provider gains support for an operation, move that operation's tests to the `providers` dataset
