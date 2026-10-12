---
paths:
  - "**/*.php"
---

# PHP Code Requirements

- PHP 8.2+, always include `declare(strict_types=1);` at the top of every PHP file
- Code must satisfy PHPStan level 7
- Supports Laravel 11.x, 12.x and 13.x; tests run against all three via Orchestra Testbench
