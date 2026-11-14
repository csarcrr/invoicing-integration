---
paths:
  - "src/Provider/**"
  - "src/Contracts/**"
  - "src/Actions/**"
  - "src/Facades/**"
---

# Provider Operations

Every operation (create, get, find, update, delete, cancel...) follows the same shape on every provider.

## Class

- An operation class lives in `src/Provider/<Provider>/<Resource>/<Operation>.php` and extends its resource base class (e.g. `src/Provider/Moloni/Item.php`), unless there is a good reason not to.
- The constructor takes the DTO, sets `$this->data`, `$this->payload = collect()` and `$this->supportedProperties`, then calls the `build*()` methods.
- `build*()` methods are `protected`. They validate what the operation needs (e.g. the id) and store the result in properties: the request URL in `$url` (declared on `src/Provider/Base.php`, read through `getUrl()`) and the request body in `$payload` (read through `getPayload()`).
- `execute()` only sends: `Http::provider()-><verb>($this->getUrl(), $this->getPayload()->toArray())`, then `Http::handleUnwantedFailures($response)`, then maps the response.
- Use `HasConfig` when the class reads provider config (units, taxes) or extends a base that declares `getConfig()`.

## Contract

- One contract per operation in `src/Contracts/IntegrationProvider/<Resource>/Should<Operation><Resource>.php`.
- It exposes `getUrl()` and `ShouldHavePayload`, so unit tests can assert the request without HTTP.
- Operations that return nothing (delete, cancel) declare `execute(): void` and do not extend `ShouldExecute`.

## Action and Facade

- Add the method to `src/Actions/<Resource>Action.php` with a `match` on `Provider` covering every case. Call `->config($this->provider->getConfig())` when the class uses `HasConfig`.
- Add the matching `@method static` line to `src/Facades/<Resource>.php`.

## Moloni

- The HTTP layer adds `company_id` to every request (`src/Configuration/HttpConfiguration.php`). Never put it in a payload.
- Moloni answers write operations with `valid`. When `valid` is not `1`, throw `RequestFailedException` with the response `message`.

## Unsupported fields

- When a provider does not support a DTO field, leave it out of the payload and document it in that provider's docs section. Never throw: throwing breaks the developer's automations.
