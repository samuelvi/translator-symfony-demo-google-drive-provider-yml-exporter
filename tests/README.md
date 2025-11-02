# Test Suite Documentation

This directory contains comprehensive tests for the Spreadsheet Translator Symfony Demo Application.

## Test Structure

```
tests/
├── Unit/                           # Unit tests - test components in isolation
│   └── Command/
│       └── TranslatorCommandTest.php
├── Integration/                    # Integration tests - test with real services
│   ├── ConfigurationTest.php
│   ├── ServiceContainerTest.php
│   └── TranslationWorkflowTest.php
├── Functional/                     # Functional tests - test from user perspective
│   └── CommandExecutionTest.php
├── Fixtures/                       # Test fixtures and sample data
│   └── sample_translations.yml
└── bootstrap.php                   # Test bootstrap file

```

## Test Categories

### Unit Tests
Tests individual components in isolation with mocked dependencies:
- **TranslatorCommandTest.php**: Tests the command logic, parameter parsing, and output formatting
  - Command configuration
  - Input parameter handling
  - Option combinations
  - Special character handling
  - Output format validation

### Integration Tests
Tests components working together with real Symfony services:

- **ServiceContainerTest.php**: Tests Symfony service container configuration
  - Service availability
  - Dependency injection
  - Autowiring
  - Service sharing

- **ConfigurationTest.php**: Tests application configuration
  - YAML configuration validity
  - Required configuration keys
  - Provider/Exporter/Shared settings
  - URL format validation

- **TranslationWorkflowTest.php**: Tests the complete translation workflow
  - End-to-end translation process
  - File generation
  - YAML format validation
  - Multiple locale support
  - File naming conventions

### Functional Tests
Tests the application from a user's perspective:

- **CommandExecutionTest.php**: Tests command execution scenarios
  - Command registration
  - Option handling
  - Output format
  - File creation
  - Edge cases and error handling

## Running Tests

### Prerequisites

```bash
# Install dependencies
composer install
```

### Running All Tests

```bash
# Using Make (with Docker)
make test

# Using Make (locally without Docker)
make test-local

# Using PHPUnit directly
vendor/bin/phpunit
```

### Running Specific Test Suites

```bash
# Unit tests only
make test-unit
# or
vendor/bin/phpunit --testsuite "Unit Tests"

# Integration tests only
make test-integration
# or
vendor/bin/phpunit --testsuite "Integration Tests"

# Functional tests only
make test-functional
# or
vendor/bin/phpunit --testsuite "Functional Tests"
```

### Running Individual Test Files

```bash
# Run a specific test file
vendor/bin/phpunit tests/Unit/Command/TranslatorCommandTest.php

# Run a specific test method
vendor/bin/phpunit --filter testExecuteWithBothOptions tests/Unit/Command/TranslatorCommandTest.php
```

### Running Tests with Coverage

```bash
# Generate HTML coverage report
make test-coverage
# or
vendor/bin/phpunit --coverage-html coverage

# View coverage report
open coverage/index.html
```

## Test Groups

Some tests are tagged with groups for selective execution:

### Network Tests
Tests that require internet connectivity to access Google Drive:

```bash
# Run only network tests
vendor/bin/phpunit --group network

# Exclude network tests (useful for offline development)
vendor/bin/phpunit --exclude-group network
```

## Test Environment

Tests run in the `test` environment with the following configuration:
- `APP_ENV=test`
- Separate test configuration in `config/packages/test/`
- Test-specific services and parameters

## Writing New Tests

### Unit Test Example

```php
public function testYourNewFeature(): void
{
    // Arrange
    $mock = $this->createMock(SomeService::class);
    $mock->expects($this->once())
        ->method('someMethod')
        ->willReturn('expected value');

    // Act
    $result = $yourObject->doSomething();

    // Assert
    $this->assertSame('expected value', $result);
}
```

### Integration Test Example

```php
public function testYourIntegration(): void
{
    self::bootKernel();
    $container = self::getContainer();

    $service = $container->get(YourService::class);
    $result = $service->process();

    $this->assertNotNull($result);
}
```

## Test Best Practices

1. **Isolation**: Unit tests should not depend on external services
2. **Clarity**: Test names should clearly describe what is being tested
3. **Completeness**: Test both success and failure scenarios
4. **Independence**: Tests should not depend on the execution order
5. **Cleanup**: Always clean up test artifacts in `tearDown()`
6. **Performance**: Mark slow tests with `@group` annotations
7. **Documentation**: Add docblocks explaining complex test scenarios

## Test Coverage Goals

- **Unit Tests**: Aim for >90% coverage of command logic
- **Integration Tests**: Cover all major workflows and configurations
- **Functional Tests**: Cover all user-facing scenarios and edge cases

## Continuous Integration

Tests are designed to run in CI/CD pipelines:

```yaml
# Example CI configuration
test:
  script:
    - composer install
    - vendor/bin/phpunit --exclude-group network
```

## Troubleshooting

### Tests Fail with Network Errors
- Check internet connectivity
- Verify Google Drive spreadsheet is publicly accessible
- Run with `--exclude-group network` to skip network tests

### Service Not Found Errors
- Clear Symfony cache: `bin/console cache:clear --env=test`
- Rebuild container: `make build`
- Verify service configuration in `config/services.yaml`

### Permission Errors
- Check `translations/` directory is writable
- Verify test environment has correct permissions

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Testing Guide](https://symfony.com/doc/current/testing.html)
- [PHPUnit Best Practices](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
