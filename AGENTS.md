# Project-Specific Rules

## Project Context

- This is a Laravel framework package for invoicing system integrations
- Package name: `csarcrr/invoicing-integration`
- Uses Pest PHP for testing
- Uses PHPStan (Larastan) for static analysis
- Uses Pint for code formatting/linting

## PHP Requirements

- **Minimum PHP version: 8.2**
- Always include `declare(strict_types=1);` at the top of all PHP files
- Follow PHP 8.2+ features and best practices

## Laravel Compatibility

- Supports Laravel 11.x and 12.x (`illuminate/contracts: ^11.0||^12.0`)
- Uses `spatie/laravel-package-tools` for package scaffolding
- Test against multiple Laravel versions using Orchestra Testbench

## Namespace Conventions

- Main source namespace: `CsarCrr\InvoicingIntegration\` → `src/`
- Database factories: `CsarCrr\InvoicingIntegration\Database\Factories\` → `database/factories/`
- Tests namespace: `CsarCrr\InvoicingIntegration\Tests\` → `tests/`
- Workbench app: `Workbench\App\` → `workbench/app/`

## Key Dependencies

- `league/iso3166` (^4.3) - ISO 3166 country codes library
- `spatie/laravel-package-tools` (^1.16) - Laravel package development utilities

## Dev Dependencies

- `larastan/larastan` (^3.0) - PHPStan for Laravel
- `laravel/pint` (^1.14) - Code formatting
- `pestphp/pest` (^3.0) - Testing framework
- `pestphp/pest-plugin-arch` (^3.0) - Architecture testing
- `pestphp/pest-plugin-laravel` (^3.0) - Laravel-specific Pest helpers
- `orchestra/testbench` (^10.3.0||^9.3.0) - Laravel package testing
- `spatie/laravel-ray` (^1.35) - Debugging tool

## Testing

- Write tests using Pest PHP syntax (not PHPUnit)
- Use Pest's expressive API (`it()`, `test()`, `expect()`, etc.)
- Use architecture tests with `pestphp/pest-plugin-arch`
- Run tests: `composer test`
- Run tests with coverage: `composer test-coverage`

## Code Quality

- Run PHPStan for static analysis before committing: `composer analyse`
- Run Pint for code formatting: `composer format`
- Follow Laravel package development conventions
- PHPStan uses deprecation rules and PHPUnit extensions

## Composer Scripts

- `composer test` - Run Pest tests
- `composer test-coverage` - Run tests with coverage report
- `composer analyse` - Run PHPStan static analysis
- `composer format` - Run Pint code formatter

## Service Provider & Facade

- Service Provider: `CsarCrr\InvoicingIntegration\InvoicingIntegrationServiceProvider`
- Facade: `CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration`
- Alias: `InvoicingIntegration`
