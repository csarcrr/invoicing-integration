---
paths:
  - "tests/**"
---

# Testing Conventions

- Write tests using Pest syntax — `it()`, `test()`, `expect()` — never raw PHPUnit
- Use `pestphp/pest-plugin-arch` for architecture tests
- Organize tests by operation under `tests/Unit/<Resource>/<Operation>/` and `tests/Feature/<Resource>/`, not by `src/` structure. An operation with many payload fields splits them into `ProviderData/`; the others keep a single `PayloadTest.php`
- Behaviour that exists only because of one provider's API (a multi-step flow, a provider-only check) goes in `tests/Feature/Providers/<Provider>/` or `tests/Unit/Providers/<Provider>/`, set to that provider in `beforeEach`. These are the only tests that skip the `providers` dataset
- Before writing a test, read a sibling test for the same kind of operation and follow its structure
- Unit tests never fake HTTP (`Http::fake`, `Http::assertSent`). They assert on what the provider class builds, through `getUrl()` and `getPayload()` (see `tests/Unit/Item/Create/ProviderData/PayloadTest.php`). HTTP faking belongs only in `tests/Feature/`
- In feature tests, fake provider HTTP with `Http::fake(mockResponse(fixtures()->...))` and no URL key; `fixtures()` already resolves the active provider
- Put per-provider differences in a `match ($provider)` inside the assertion (e.g. inside `Http::assertSent`), including when providers behave differently, instead of one copied test per provider
- Use `match ($provider)` only for mapping differences (key names, URLs). Tests stay agnostic: never assert a provider's own rules, such as a required field. Build test data every provider can accept
- When every provider throws the same exception, write one test on the `providers` dataset and assert only the exception class, not the message
- A test closure that does not use `$provider` omits the parameter; the `providers` dataset still configures the provider
- Give both providers' fixtures the same values where the provider format allows it, so the expected values stay shared
- Name response fixtures after the operation (`create.json`, `update.json`, `fetch.json`), each holding that provider's real reply, as in `tests/Fixtures/IntegrationProvider/*/Response/Item/`. Every provider gets the same file name, so the test runs on `->with('providers', ['create'])`. Never pair each provider with its own fixture name in a dataset (`'vendus' => [fn () => vendusProvider(), 'response']`): a new provider never runs that test
- Every test runs on the `providers` dataset, so a new provider runs it by default. Outside the `Providers/<Provider>/` folders, never pin a test to one provider with `mockConfiguration(Provider::X)`
- When a test does not apply to a provider (the operation or behaviour is not supported there), skip that provider at the top of the test with `$this->markTestSkipped('<reason>')`. Pest's `->skip()` condition cannot see the dataset provider
- The structure test is the one place that asserts `OperationNotSupportedException`
