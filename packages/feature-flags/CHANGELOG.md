# Changelog

All notable changes to `magetech/laravel-feature-flags` will be documented in this file.

## [1.0.0] - 2026-08-25

### Added
- Boolean feature flags
- Percentage rollouts with deterministic hashing
- Variant/A-B testing
- Configuration flags
- User targeting (ID, email, role, permission)
- Team and organization targeting
- Environment-specific configuration
- User-specific overrides with precedence rules
- Scheduling (start/end dates)
- Blade directives (`@feature`, `@unlessfeature`, `@featureVariant`)
- Middleware (`feature:key`)
- Artisan commands (list, create, enable, disable, check, clear-cache, export, import)
- REST API endpoints
- Cache support with configurable TTL
- Events (created, updated, deleted, enabled, disabled, evaluated, override)
- Extensible rule engine with contracts
- Authorization policies
- Full test suite with Pest
