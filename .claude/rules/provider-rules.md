# Provider Rules Are the Provider's Job

The package makes the provider APIs easier to use and lets a developer switch providers with little effort. The code does not account for each provider's business rules.

- A provider's own requirement (a required field, a document needing a client address, a payment that must match the total) is not handled in code. Don't validate it, default it, or work around it, and don't report it as a package bug.
- When the developer omits something a provider requires, send the request as built and let the provider's error reach the developer through the usual exception.
- The package's job is to map: every field the developer sets must reach the provider in the shape the provider expects. Fix only cases where the package drops, renames or mis-shapes data, or where no input can produce a field the provider needs.
- Provider rules a developer is likely to hit may be documented in that provider's section of the docs, as a note for the developer. The note informs; the code stays unchanged.
