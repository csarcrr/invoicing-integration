---
paths:
  - "tests/**"
---

# Testing Conventions

- Write tests using Pest syntax — `it()`, `test()`, `expect()` — never raw PHPUnit
- Use `pestphp/pest-plugin-arch` for architecture tests
- Tests mirror `src/` structure under `tests/Unit/`
- Before writing a test, read a sibling test for the same kind of operation and follow its structure
- Fake provider HTTP with `Http::fake(mockResponse(fixtures()->...))` and no URL key; `fixtures()` already resolves the active provider
- Put per-provider differences in a `match ($provider)` inside the assertion (e.g. inside `Http::assertSent`)
- When a provider does not support an operation, the feature test calls `$this->markTestSkipped('<Provider> does not support <operation>.')`; it never passes silently or returns early. The structure test is the one place that asserts `OperationNotSupportedException`
- A skip message names the exact unsupported operation. When a provider gains or loses support for an operation, update every skip message that mentions it
