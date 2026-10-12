# Simplicity First

- Write the minimum code that solves the problem. Add no features, config options, or flexibility that were not requested.
- No abstractions for single-use code. A new contract, trait, or helper needs more than one consumer.
- No error handling for impossible scenarios. Validate at the boundaries (DTOs, provider responses) and trust internal code.
- If it could be done in a quarter of the lines, rewrite it. Ask: "Would a senior engineer call this overcomplicated?"
