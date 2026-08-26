# Changelog

All notable changes to `magetech/laravel-audit` will be documented in this file.

## [1.0.0] - 2024-01-01

### Added

- Initial release
- Model auditing with automatic event tracking
- Before/after change tracking
- Manual audit recording with fluent API
- Custom event support
- Actor resolution (authenticated user, API token, system)
- Request context capture (IP, user agent, URL, route, etc.)
- Multi-tenancy support with configurable tenant resolver
- Batch operations with UUID grouping
- Field exclusion for sensitive data
- Field masking with customizable strategies
- Hash chaining for tamper evidence (SHA-256)
- REST API endpoints for querying audits
- Authorization policy with configurable permissions
- CSV and JSON export
- Configurable retention policies
- Queue support for async processing
- Multi-database support
- Audit statistics command
- Integrity verification command
- Cleanup and prune commands
- Blade views for optional dashboard
- Comprehensive test suite
