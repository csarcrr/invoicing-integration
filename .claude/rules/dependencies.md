# Dependencies

- Before relying on how a dependency behaves (Spatie Laravel Data casting, validation, rule inference, or any other package), read its source in `vendor/`. Base the code on what the source does, not on assumptions.
- Pass nested DTOs and collections to `make()` as arrays. Spatie builds them from the property type or its `@var` docblock; a `Collection` or `Data` instance fails the inferred `array` rule.
