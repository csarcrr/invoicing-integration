---
paths:
  - "**/*.php"
---

# PHP Code Requirements

- PHP 8.2+, always include `declare(strict_types=1);` at the top of every PHP file
- Code must satisfy PHPStan level 7
- Supports Laravel 11.x, 12.x and 13.x; tests run against all three via Orchestra Testbench
- Use early returns: handle the case that does not apply first and return, so the main path needs no `else` or `elseif`
- Every provider operation class extends its resource base class (e.g. `src/Provider/Moloni/Item.php`) unless there is a good reason not to
- A provider operation builds its request URL in a method and stores it in the `$url` property of `src/Provider/Base.php`, read through `getUrl()`
