# Changelog

All notable changes to magetech/laravel-query-toolkit will be documented in this file.

## [1.0.0] - 2026-08-24

### Added
- Initial release
- QueryBuilder orchestrator class
- QueryBuilderRequest for request parsing
- Filter system with 13+ filter types:
    - ExactFilter
    - PartialFilter
    - BooleanFilter
    - NumericFilter
    - DateFilter
    - DateRangeFilter
    - EnumFilter
    - ScopeFilter
    - CallbackFilter
    - RelationshipFilter
    - NestedRelationshipFilter
    - JSONFilter
- Sort system with default and macro sorts
- Include system with default, count, and macro includes
- Search system with LIKE and full-text drivers
- Pagination support (standard and cursor)
- Field selection (sparse fieldsets)
- Middleware for query validation
- Artisan commands for scaffolding
- Helper functions
- Facade support
- Comprehensive test suite
- PHPStan level 6 compatibility
- Laravel Pint configuration
