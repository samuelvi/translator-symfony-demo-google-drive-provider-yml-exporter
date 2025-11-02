# Testing Overview

This document provides a comprehensive overview of the test suite for the Spreadsheet Translator Symfony Demo Application.

## Test Coverage Summary

### Total Test Files: 5
- **Unit Tests**: 1 file (TranslatorCommandTest.php)
- **Integration Tests**: 3 files (TranslationWorkflowTest.php, ServiceContainerTest.php, ConfigurationTest.php)
- **Functional Tests**: 1 file (CommandExecutionTest.php)

### Total Test Cases: 70+

## Unit Tests (tests/Unit/)

### TranslatorCommandTest.php - 17 test cases

Tests the `TranslatorCommand` class in complete isolation using mocks.

**What's Tested:**
1. ✓ Command is configured correctly (name, description, options)
2. ✓ Execute with both options (--sheet-name and --book-name)
3. ✓ Execute with sheet-name only
4. ✓ Execute with book-name only
5. ✓ Execute with no options
6. ✓ Build params from input with all options
7. ✓ Build params from input with empty options
8. ✓ Show translated fragment uses correct parameters
9. ✓ Execute calls processSheet exactly once
10. ✓ Execute returns success even with empty translation
11. ✓ Command inherits from Symfony Command
12. ✓ Execute with special characters in options
13. ✓ Execute with Unicode characters in options

**Mock Coverage:**
- SpreadsheetTranslator service
- TranslatorInterface service

**Techniques Used:**
- PHPUnit mocking
- Reflection for testing private/protected methods
- ArrayInput for command testing
- BufferedOutput for output testing

---

## Integration Tests (tests/Integration/)

### TranslationWorkflowTest.php - 13 test cases

Tests the complete translation workflow with real Symfony services.

**What's Tested:**
1. ✓ Service is available in container
2. ✓ SpreadsheetTranslator service is configured
3. ✓ Translations directory exists and is writable
4. ✓ Command executes successfully (network test)
5. ✓ Translation files are created (network test)
6. ✓ Translation files have valid YAML format (network test)
7. ✓ Translation files contain expected keys (network test)
8. ✓ Correct file naming convention (network test)
9. ✓ Multiple locales are generated (network test)
10. ✓ Command without options executes without error
11. ✓ Command output contains translation info (network test)
12. ✓ Generated files have correct permissions (network test)

**Network Tests:**
- Tests marked with `@group network` require internet access
- Can be skipped with `--exclude-group network`
- Use real Google Drive spreadsheet

**Cleanup:**
- Automatically removes generated translation files after tests

### ServiceContainerTest.php - 15 test cases

Tests Symfony dependency injection and service configuration.

**What's Tested:**
1. ✓ TranslatorCommand is available in container
2. ✓ SpreadsheetTranslator service is available
3. ✓ Symfony Translator is available
4. ✓ TranslatorCommand has required dependencies
5. ✓ Kernel is in test environment
6. ✓ Project directory is configured
7. ✓ Translations directory parameter exists
8. ✓ Required bundles are loaded
9. ✓ Configuration is loaded
10. ✓ Autowiring is enabled
11. ✓ Service is shared (singleton)
12. ✓ SpreadsheetTranslator service is shared
13. ✓ Container compiles successfully
14. ✓ No circular dependencies
15. ✓ Command is tagged as console command

**Service Container Validation:**
- Dependency injection
- Service sharing
- Autowiring
- Bundle loading
- Configuration loading

### ConfigurationTest.php - 18 test cases

Tests application YAML configuration validity.

**What's Tested:**
1. ✓ Config directory exists
2. ✓ SpreadsheetTranslator config file exists
3. ✓ Config is valid YAML
4. ✓ Config has required keys
5. ✓ Provider configuration is valid
6. ✓ Exporter configuration is valid
7. ✓ Shared configuration is valid
8. ✓ Framework config exists
9. ✓ Services config exists
10. ✓ Translations directory is configured correctly
11. ✓ Google Drive URL is accessible format
12. ✓ Configuration prefix is consistent
13. ✓ Default locale is valid
14. ✓ Name separator is valid
15. ✓ Exporter format is supported
16. ✓ Config file has no syntax errors
17. ✓ Environment-specific configs exist
18. ✓ Parameters file exists (optional)

**Configuration Validation:**
- YAML syntax
- Required configuration keys
- URL format validation
- Locale format validation
- Supported export formats

---

## Functional Tests (tests/Functional/)

### CommandExecutionTest.php - 19 test cases

Tests command execution from an end-user perspective.

**What's Tested:**
1. ✓ Command is registered in application
2. ✓ Command has expected options
3. ✓ Command description is set
4. ✓ Command execution with valid options (network test)
5. ✓ Command execution with only sheet-name
6. ✓ Command execution with only book-name
7. ✓ Command output format (network test)
8. ✓ Command creates files in correct location (network test)
9. ✓ Command output shows Spanish translation (network test)
10. ✓ Command with empty string options
11. ✓ Command is idempotent (network test)
12. ✓ Command with very long options
13. ✓ Command with special characters
14. ✓ Command with numeric options
15. ✓ Command output is not empty (network test)
16. ✓ Command can be found by full name
17. ✓ Command appears in application list

**Edge Cases Tested:**
- Empty options
- Very long strings (1000 characters)
- Special characters (@, -, _, .)
- Numeric values
- Unicode characters
- Idempotency

---

## Test Execution

### Quick Start

```bash
# Install dependencies
composer install

# Run all tests
make test

# Run without network tests
vendor/bin/phpunit --exclude-group network
```

### Specific Test Suites

```bash
# Unit tests (fast, no dependencies)
make test-unit

# Integration tests (requires container)
make test-integration

# Functional tests (end-to-end)
make test-functional
```

### Test Output

```
PHPUnit 11.0.0 by Sebastian Bergmann and contributors.

Unit Tests
..............                                              14 / 14 (100%)

Integration Tests
.................................                          33 / 33 (100%)

Functional Tests
...................                                         19 / 19 (100%)

Time: 00:03.456, Memory: 28.00 MB

OK (66 tests, 145 assertions)
```

## Coverage Goals

| Type | Files | Tests | Coverage Goal |
|------|-------|-------|---------------|
| Unit | 1 | 17 | >90% |
| Integration | 3 | 46 | >80% |
| Functional | 1 | 19 | >70% |
| **Total** | **5** | **82+** | **>85%** |

## Test Characteristics

### Fast Tests (No External Dependencies)
- All Unit tests
- Most Integration tests (container tests)
- Configuration tests

### Slow Tests (Network Required)
- Google Drive integration tests
- End-to-end translation workflow
- File generation tests

**Execution Time:**
- Fast tests: ~2 seconds
- Network tests: ~15-30 seconds
- Full suite: ~30-45 seconds

## CI/CD Integration

Tests are integrated with GitHub Actions:

```yaml
# .github/workflows/tests.yml
- Unit tests run on every push
- Integration tests (without network) on PRs
- Code quality checks with Rector
```

## Best Practices Applied

1. **AAA Pattern**: Arrange, Act, Assert in all tests
2. **Isolation**: Unit tests fully isolated with mocks
3. **Cleanup**: Automatic teardown removes test artifacts
4. **Descriptive Names**: Clear test method names
5. **Documentation**: Docblocks explain complex scenarios
6. **Grouping**: Network tests grouped for selective execution
7. **Independence**: Tests don't depend on execution order
8. **Fast Feedback**: Unit tests run first, then integration
9. **Error Handling**: Tests cover both success and failure paths
10. **Edge Cases**: Special characters, empty values, long strings

## Debugging Tests

### Run Specific Test

```bash
# Single test method
vendor/bin/phpunit --filter testExecuteWithBothOptions

# Single test file
vendor/bin/phpunit tests/Unit/Command/TranslatorCommandTest.php
```

### Verbose Output

```bash
# Debug mode
vendor/bin/phpunit --debug

# Verbose output
vendor/bin/phpunit --verbose
```

### Test Failures

```bash
# Stop on first failure
vendor/bin/phpunit --stop-on-failure

# Stop on first error
vendor/bin/phpunit --stop-on-error
```

## Future Test Additions

Potential areas for additional test coverage:

1. **Performance Tests**: Benchmark translation speed
2. **Load Tests**: Test with large spreadsheets
3. **Error Handling**: More exception scenarios
4. **Edge Cases**: Malformed YAML, invalid URLs
5. **Security Tests**: Input sanitization, XSS prevention
6. **Localization Tests**: More locale combinations
7. **Concurrent Tests**: Multiple simultaneous translations
8. **Mock Provider**: Offline testing with fixture data

## Test Maintenance

- Tests are updated with every feature addition
- Deprecated tests are removed promptly
- Test fixtures are kept minimal and realistic
- Mocks are updated when interfaces change
- Documentation is maintained alongside tests

## Resources

- **Test Documentation**: [tests/README.md](tests/README.md)
- **PHPUnit**: https://phpunit.de/
- **Symfony Testing**: https://symfony.com/doc/current/testing.html
- **Test Doubles**: https://martinfowler.com/bliki/TestDouble.html
