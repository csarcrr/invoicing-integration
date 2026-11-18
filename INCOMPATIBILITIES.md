# Incompatibilities

Fields that one or more providers do not support for an operation. The package does not support these fields for that operation, so it never sends them to any provider.

## Invoice cancel

| Field | Vendus | Moloni | Reason |
| --- | --- | --- | --- |
| `notes` | Not supported | Supported as `observation` | `PATCH documents/{id}` accepts only `id`, `stock`, `status` and `mode` (`providers-docs/vendus/openapi.json`, `Documents_PATCH`). Moloni `documents/documentCancel` accepts an `observation` (`providers-docs/moloni/dev/documents/documents/documentcancel/`). |
