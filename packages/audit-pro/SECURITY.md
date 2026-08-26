# Security Policy

## Reporting Vulnerabilities

If you discover a security vulnerability within MTS Laravel Audit Pro, please send an email to MageTech Solutions at info@magetechsol.com. All security vulnerabilities will be promptly addressed.

## Security Considerations

### Audit Record Immutability

Audit records are designed to be immutable. The package does not provide normal update operations on audit records. Hash chaining provides tamper evidence, not absolute tamper prevention.

### Sensitive Data

- The package never logs passwords, API tokens, or authentication secrets by default
- Configurable field exclusion and masking protect sensitive data
- Always review the `exclude` and `masking` configuration

### Authorization

- All API endpoints require authentication
- Authorization policies protect audit record access
- Configure appropriate permissions for your use case

### Tenant Isolation

When multi-tenancy is enabled, audit records are automatically scoped to the current tenant. One tenant cannot query another tenant's audit records.

### Rate Limiting

API endpoints include rate limiting to prevent abuse. Configure limits in `config/audit.php`.

### Input Validation

All input is validated and sanitized. Mass assignment protection is enforced on the Audit model.

### SQL Injection Prevention

The package uses parameterized queries throughout. No raw SQL is constructed from user input.

### Serialization

The package prevents circular references during serialization and limits string lengths to prevent memory issues.
