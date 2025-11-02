# Testing Quick Reference

Quick commands and examples for testing the Spreadsheet Translator Demo.

## 🚀 Quick Start

```bash
# Install dependencies
composer install

# Run all tests
make test

# Run tests locally (no Docker)
bin/phpunit
```

## 📋 Common Commands

### Run All Tests
```bash
make test                    # With Docker
make test-local              # Without Docker
bin/phpunit                  # Direct PHPUnit
```

### Run Specific Test Suites
```bash
make test-unit              # Unit tests only
make test-integration       # Integration tests only
make test-functional        # Functional tests only
```

### Run Offline (Skip Network Tests)
```bash
bin/phpunit --exclude-group network
```

### Run Specific Test File
```bash
bin/phpunit tests/Unit/Command/TranslatorCommandTest.php
```

### Run Specific Test Method
```bash
bin/phpunit --filter testExecuteWithBothOptions
```

### Generate Coverage Report
```bash
make test-coverage
open coverage/index.html
```

## 🔍 Test Execution Options

### Stop on First Failure
```bash
bin/phpunit --stop-on-failure
```

### Verbose Output
```bash
bin/phpunit --verbose
```

### Debug Mode
```bash
bin/phpunit --debug
```

### List All Tests
```bash
bin/phpunit --list-tests
```

### Test Specific Group
```bash
bin/phpunit --group network
```

## 📂 Test File Locations

```
tests/
├── Unit/Command/TranslatorCommandTest.php          # 17 tests
├── Integration/
│   ├── TranslationWorkflowTest.php                 # 13 tests
│   ├── ServiceContainerTest.php                    # 15 tests
│   └── ConfigurationTest.php                       # 18 tests
├── Functional/CommandExecutionTest.php             # 19 tests
└── Fixtures/
    ├── TestHelperTrait.php                         # Test utilities
    └── sample_translations.yml                     # Sample data
```

## 🎯 Test Coverage by Type

| Type | Files | Tests | Speed |
|------|-------|-------|-------|
| Unit | 1 | 17 | ⚡ Fast |
| Integration | 3 | 46 | ⚡ Fast (🌐 Slow with network) |
| Functional | 1 | 19 | ⚡ Fast (🌐 Slow with network) |

## 💡 Quick Tips

### 1. Run Fast Tests First
```bash
# Unit tests are fastest
bin/phpunit --testsuite "Unit Tests"

# Then integration without network
bin/phpunit --testsuite "Integration Tests" --exclude-group network
```

### 2. Debugging Failed Tests
```bash
# Stop on failure and show details
bin/phpunit --stop-on-failure --verbose
```

### 3. Watch Mode (Manual)
```bash
# Run tests on file change (requires entr or similar)
find tests/ src/ -name "*.php" | entr bin/phpunit
```

### 4. Check Test Configuration
```bash
# Verify PHPUnit configuration
bin/phpunit --configuration phpunit.xml.dist --list-tests
```

### 5. Clear Test Cache
```bash
rm -rf .phpunit.cache
```

## 🐛 Troubleshooting

### Tests Failing?
1. **Clear cache**: `rm -rf .phpunit.cache var/cache/test`
2. **Reinstall**: `composer install`
3. **Check permissions**: `chmod -R 777 translations/`
4. **Verify config**: Check `config/packages/atico_spreadsheet_translator.yaml`

### Network Tests Timeout?
```bash
# Skip network tests
bin/phpunit --exclude-group network
```

### Service Not Found?
```bash
# Clear Symfony cache
bin/console cache:clear --env=test
```

## 📊 Test Examples

### Example 1: Test Command Execution
```php
public function testCommandExecutes(): void
{
    $input = new ArrayInput(['--sheet-name' => 'common']);
    $output = new BufferedOutput();

    $statusCode = $this->command->run($input, $output);

    $this->assertSame(Command::SUCCESS, $statusCode);
}
```

### Example 2: Test Service Container
```php
public function testServiceExists(): void
{
    $container = self::getContainer();
    $this->assertTrue($container->has(MyService::class));
}
```

### Example 3: Test Configuration
```php
public function testConfigIsValid(): void
{
    $config = Yaml::parseFile('config/packages/my_bundle.yaml');
    $this->assertArrayHasKey('my_bundle', $config);
}
```

## 🎨 Test Naming Convention

```php
// Good test names
testCommandExecutesSuccessfully()
testValidationFailsWithEmptyInput()
testServiceIsAvailableInContainer()

// Bad test names
test1()
testCommand()
testIt()
```

## 📝 Assertion Examples

```php
// Common assertions
$this->assertTrue($value);
$this->assertFalse($value);
$this->assertSame($expected, $actual);
$this->assertEquals($expected, $actual);
$this->assertStringContainsString('text', $string);
$this->assertMatchesRegularExpression('/pattern/', $string);
$this->assertFileExists($path);
$this->assertDirectoryExists($path);
$this->assertArrayHasKey('key', $array);
$this->assertInstanceOf(Class::class, $object);
$this->assertCount(5, $array);
$this->assertEmpty($array);
$this->assertNotEmpty($array);
```

## 🔧 Makefile Shortcuts

```bash
make help          # Show all available commands
make test          # Run all tests
make test-unit     # Unit tests
make test-int      # Integration tests (shortcut)
make test-func     # Functional tests (shortcut)
make test-cov      # Coverage report (shortcut)
```

## 📚 Additional Resources

- **Full Documentation**: [tests/README.md](README.md)
- **Test Overview**: [../TESTING.md](../TESTING.md)
- **PHPUnit Docs**: https://phpunit.de/
- **Symfony Testing**: https://symfony.com/doc/current/testing.html

## ⚡ Performance Tips

1. **Run unit tests frequently** - They're fast!
2. **Skip network tests during development** - Use `--exclude-group network`
3. **Use `--stop-on-failure`** - Find problems faster
4. **Run specific tests** - Don't run everything all the time
5. **Generate coverage only when needed** - It's slower

## 🎯 Pre-Commit Checklist

Before committing code:

```bash
# 1. Run all tests
make test

# 2. Check code quality
make rector-check

# 3. Verify no network dependency in unit tests
bin/phpunit --testsuite "Unit Tests"
```

---

**Happy Testing! 🎉**
