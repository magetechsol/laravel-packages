# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within MTS Laravel Query Toolkit, please send an email to security@magetechsolutions.com. All security vulnerabilities will be promptly addressed.

**Please do NOT report security vulnerabilities through public GitHub issues.**

## Disclosure Policy

When the security team receives a security bug report, they will assign it to a primary handler. This person will coordinate the fix and release process, involving the following steps:

1. Confirm the problem and determine the affected versions
2. Audit code to find any potential similar problems
3. Prepare fixes for all releases still under maintenance
4. Release new security version(s)

## Security Related Configuration

When using this package, ensure:

1. All filters are explicitly whitelisted
2. The ignore_invalid_filters option is set to alse in production
3. The max_per_page setting is reasonable for your use case
4. The middleware ValidateQueryParameters is applied to your API routes

## Acknowledgments

We would like to thank all security researchers who responsibly disclose vulnerabilities.
