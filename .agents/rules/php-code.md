---
paths:
  - "**/*.php"
---

# PHP Code Requirements

- PHP 8.2+, always include `declare(strict_types=1);` at the top of every PHP file
- Code must satisfy PHPStan level 7
- Supports Laravel 11.x, 12.x and 13.x; tests run against all three via Orchestra Testbench
- Use early returns: handle the case that does not apply first and return, so the main path needs no `else` or `elseif`
- Use `is_null($x)` / `! is_null($x)` for null checks, not `=== null` / `!== null`
- Use `protected` properties instead of `const` for values a class uses, so subclasses can override them
