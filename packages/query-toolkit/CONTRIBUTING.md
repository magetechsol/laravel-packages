# Contributing to MTS Laravel Query Toolkit

Thank you for considering contributing to MTS Laravel Query Toolkit!

## Code of Conduct

Please read our [Code of Conduct](CODE_OF_CONDUCT.md) before contributing.

## How to Contribute

### Reporting Bugs

1. Check existing issues on GitHub
2. Create a new issue with:
    - Clear title and description
    - Steps to reproduce
    - Expected vs actual behavior
    - Laravel and PHP versions

### Suggesting Features

1. Check existing feature requests
2. Create a new issue with:
    - Clear title and description
    - Use case
    - Proposed solution

### Pull Requests

1. Fork the repository
2. Create a feature branch: git checkout -b feature/my-feature
3. Install dependencies: composer install
4. Make your changes
5. Run tests: composer test
6. Run code style: composer format-test
7. Run static analysis: composer analyse
8. Commit your changes
9. Push to your fork
10. Create a pull request

### Pull Request Guidelines

- Follow PSR-12 coding standards
- Add tests for new features
- Update documentation
- Keep commits focused and atomic
- Write clear commit messages

## Development Setup

`ash
git clone https://github.com/magetechsolutions/laravel-query-toolkit.git
cd laravel-query-toolkit
composer install
composer test
`

## Running Tests

`ash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test
vendor/bin/pest tests/Feature/FilterTest.php
`

## Code Style

This project uses Laravel Pint:

`ash
# Check code style
composer format-test

# Fix code style
composer format
`

## Static Analysis

This project uses PHPStan:

`ash
composer analyse
`

## License

By contributing, you agree that your contributions will be licensed under the MTS License.
