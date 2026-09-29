---
paths:
  - "src/Data/**"
---

# DTOs

All domain data uses `Spatie\LaravelData`. DTOs validate on construction. Use `Optional` (from `src/Helpers/Properties.php`) when a field may be intentionally absent vs. `null`.
