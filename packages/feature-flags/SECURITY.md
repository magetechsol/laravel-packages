# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please send an email to dev@magetechsolutions.com. All security vulnerabilities will be promptly addressed.

Please do not report security vulnerabilities through public GitHub issues.

## Security Best Practices

This package implements the following security measures:

- Input validation on all API endpoints
- Mass assignment protection on all models
- Authorization checks on management APIs
- Rate limiting on API endpoints
- No use of `eval()` or unsafe expression evaluation
- Safe rule evaluation through contract-based architecture
- Secure JSON handling
- Authentication required for API access
