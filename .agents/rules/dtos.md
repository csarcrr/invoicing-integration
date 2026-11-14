---
paths:
  - "src/Data/**"
---

# DTOs

All domain data uses `Spatie\LaravelData`. DTOs validate on construction. Use `Optional` (from `src/Helpers/Properties.php`) when a field may be intentionally absent vs. `null`.

## One field set for every provider

- Never add a DTO field that only one provider uses. Every field must map onto every provider that supports the operation.
- A provider must work with the fields the DTO already has. When it needs a value that no shared field carries, its provider class builds that value itself (e.g. a random token for a required code) and does not ask the developer for it.
- A value the package generates, and any provider-only field in a response, is kept in the DTO's additional data (`additional()`, read with `getAdditionalData()`), not in a new property.
