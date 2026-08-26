# Contributing to MTS Laravel Audit Pro

Thank you for considering contributing to MTS Laravel Audit Pro! This document provides guidelines and information for contributors.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

## How to Contribute

### Reporting Bugs

1. Check existing issues to avoid duplicates
2. Create a new issue with a clear title and description
3. Include steps to reproduce the bug
4. Include your environment details (PHP version, Laravel version, etc.)

### Suggesting Features

1. Check existing issues and discussions
2. Create a new issue with the "feature request" label
3. Describe the feature and its use case
4. Explain why it would be valuable

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch from `main`
3. Make your changes
4. Add or update tests
5. Update documentation if needed
6. Submit a pull request

## Development Setup

### Requirements

- PHP 8.2+
- Composer
- Laravel 11.x or 12.x

### Installation

```bash
git clone https://github.com/magetechsol/laravel-audit.git
cd laravel-audit
composer install
```

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage
```

### Code Style

This project uses Laravel Pint for code formatting:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

## Coding Guidelines

### PHP Standards

- Use PHP 8.2+ features where appropriate
- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Use type hints and return types
- Use meaningful variable and method names

### Laravel Conventions

- Follow Laravel conventions and patterns
- Use service containers for dependency injection
- Use facades where appropriate
- Use Eloquent models and relationships

### Testing

- Write tests for new features
- Maintain test coverage
- Use descriptive test names
- Follow the Arrange-Act-Assert pattern

### Documentation

- Update README.md for user-facing changes
- Update CHANGELOG.md with your changes
- Add PHPDoc blocks for public methods

## Pull Request Process

1. Update the README.md with details of changes if applicable
2. Update the CHANGELOG.md following the existing format
3. The PR will be merged once approved by a maintainer

## Questions?

If you have questions about contributing, please open an issue or contact us at info@magetechsol.com.

---

Developed by [MageTech Solutions](https://www.magetechsol.com/)
