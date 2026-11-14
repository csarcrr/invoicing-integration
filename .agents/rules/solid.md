---
paths:
  - "src/**/*.php"
---

# SOLID Principles

Follow SOLID as a guide, not dogma. When a principle conflicts with Simplicity First, simplicity wins; don't add interfaces or layers just to satisfy a principle.

- **Single Responsibility**: each class has one reason to change. A provider class like `Provider/<Provider>/Invoice/Create` builds and sends one request; mapping, validation, and HTTP concerns stay in their own places (DTOs, `Http::handleUnwantedFailures()`).
- **Open/Closed**: add behaviour by adding new classes (a new provider, a new operation) rather than editing unrelated existing ones. The `match` on `Provider` in Actions is the accepted extension point.
- **Liskov Substitution**: every provider implementation must honour its contract fully: same inputs, same return types, domain exceptions instead of provider-specific ones.
- **Interface Segregation**: keep contracts small and operation-focused (`ShouldCreateInvoice`, `CreateClient`); a provider implements only the operations it supports.
- **Dependency Inversion**: Actions and Facades depend on contracts, not concrete provider classes. Inject dependencies instead of reaching for static or global state.
