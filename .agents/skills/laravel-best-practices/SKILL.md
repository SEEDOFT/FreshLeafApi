---
name: laravel-best-practices
description: "Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns."
license: MIT
metadata:
  author: laravel
---

# Laravel Best Practices (FreshLeaf)

## 0. PHP 8.5 Modern Standards (MANDATORY)

- **Property Hooks**: Use for all calculated model properties (e.g., `activePrice`). Do not use legacy getter/setter methods.
- **Asymmetric Visibility**: Use `public private(set)` for properties that should be readonly externally but modifiable by model logic.
- **Pipe Operator (`|>`)**: Use for multi-step data or price transformations to ensure readable code.
- **Strict Types**: Every PHP file MUST start with `declare(strict_types=1);`.

## 1. Database & History Architecture

- **Direct Model Events**: For simple history or audit logging (e.g., `PriceHistory`, `ExchangeRateHistory`), use the model's `booted()` method with `static::saved()` or `static::saving()`. **Avoid external Observers** for these tasks to keep logic centralized.
- **Consistent Precision**: Use `decimal(15, 4)` for all currency, rates, and financial balances to match the project's "Wallet Style."
- **Eager Loading**: Always use `with()` to prevent N+1 queries.

## 2. Advanced Query Patterns
...
- `addSelect()` subqueries over eager-loading entire has-many for a single value.
- `whereIn` + `pluck()` over `whereHas` for better index usage.

## 3. Security
- Define `#[Fillable]` and `#[Hidden]` attributes on every model.
- Authorize actions via Policies; ensure `UserPolicy` allows owners to access their own profiles even without sub-profile records.

## 4. Validation & Forms
- Use Form Request classes. Prefer array notation `['required', 'string']`.
- Use `$request->validated()` only.

## 5. UI (Filament)
- **Modern Polish**: Use Glassmorphism (backdrop blur), soft shadows, and rounded corners (1rem+) for all panels.
- **SPA Mode**: Always enable `->spa()` in Panel Providers for the "Vue/React" feel.

## How to Apply
1. Check sibling files for existing patterns (Consistency First).
2. Follow PHP 8.5 conventions for all new code.
3. Verify syntax with `php artisan tinker`.
