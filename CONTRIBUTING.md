# Contributing to Donate Nova

Thank you for your interest in contributing to Donate Nova! This document provides guidelines for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/donate-nova.git`
3. Create a new branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Commit your changes: `git commit -m "Add your feature"`
6. Push to your fork: `git push origin feature/your-feature-name`
7. Create a pull request

## Development Setup

Follow the installation instructions in [README.md](README.md) to set up your local development environment.

## Coding Standards

### PHP
- Follow PSR-12 coding standards
- Use type hints where possible
- Write descriptive variable and function names
- Add PHPDoc comments for classes and methods

### JavaScript
- Use ES6+ features
- Follow consistent indentation (2 spaces)
- Use meaningful variable names

### Blade Templates
- Use proper indentation
- Follow Laravel Blade conventions
- Keep logic minimal in views

## Commit Message Guidelines

Use clear and descriptive commit messages:

- `feat: Add new donation export feature`
- `fix: Resolve bKash callback issue`
- `docs: Update README with deployment steps`
- `style: Format code according to PSR-12`
- `refactor: Simplify donation controller logic`
- `test: Add tests for payment flow`

## Pull Request Process

1. Update documentation if needed
2. Add tests for new features
3. Ensure all tests pass: `php artisan test`
4. Update CHANGELOG.md with your changes
5. Ensure your code follows the project's coding standards
6. Request review from maintainers

## Testing

Before submitting a pull request:

```bash
# Run tests
php artisan test

# Check code style
./vendor/bin/pint --test

# Run static analysis (if configured)
./vendor/bin/phpstan analyse
```

## Reporting Bugs

When reporting bugs, please include:

- Description of the issue
- Steps to reproduce
- Expected behavior
- Actual behavior
- Laravel version
- PHP version
- Any relevant error messages or logs

## Feature Requests

We welcome feature requests! Please:

- Check if the feature has already been requested
- Provide a clear description of the feature
- Explain the use case and benefits
- Be open to discussion and feedback

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Help others learn and grow

## Questions?

If you have questions, feel free to:
- Open an issue
- Contact the maintainers
- Check the documentation

Thank you for contributing to Donate Nova!
