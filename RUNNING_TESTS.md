# Running Tests - Quick Guide

## Where Are You Running Tests?

### 🐳 Inside Docker Container

If you see a prompt like `root@xxxxx:/app#`, you're **inside** the container.

**Run tests directly with PHPUnit:**

```bash
# Run all tests
bin/phpunit

# Run specific test suite
bin/phpunit --testsuite "Unit Tests"
bin/phpunit --testsuite "Integration Tests"
bin/phpunit --testsuite "Functional Tests"

# Run without network tests (for offline development)
bin/phpunit --exclude-group network

# Run with colors
bin/phpunit --colors=always

# Stop on first failure
bin/phpunit --stop-on-failure

# Verbose output
bin/phpunit --verbose
```

### 💻 On Host Machine (Outside Docker)

If you see your normal terminal prompt, you're **outside** the container.

**Use Make commands:**

```bash
# Run all tests
make test

# Run specific test suite
make test-unit
make test-integration
make test-functional

# Generate coverage report
make test-coverage
```

**Or run without Make:**

```bash
# If you have PHP and composer installed locally
bin/phpunit
```

## Quick Access Guide

### Getting Into Docker Container

```bash
# From host machine
make shell
# or
docker-compose -f docker/docker-compose.yaml exec php-atic-gy bash
```

### Exiting Docker Container

```bash
# Inside container
exit
```

## Common Test Commands

### Inside Container

```bash
# Fast: Unit tests only (no external dependencies)
bin/phpunit --testsuite "Unit Tests"

# Medium: Integration tests without network
bin/phpunit --testsuite "Integration Tests" --exclude-group network

# Full: All tests including network tests
bin/phpunit

# Specific test file
bin/phpunit tests/Unit/Command/TranslatorCommandTest.php

# Specific test method
bin/phpunit --filter testExecuteWithBothOptions
```

### From Host Machine

```bash
# Run all tests through Docker
make test

# Run specific suite through Docker
make test-unit
make test-integration
make test-functional
```

## Troubleshooting

### Error: "make: docker-compose: No such file or directory"

**Problem**: You're trying to use `make` commands inside the Docker container.

**Solution**: Use `bin/phpunit` directly when inside the container.

```bash
# ❌ Don't do this inside container
make test

# ✅ Do this inside container
bin/phpunit
```

### Error: "bin/phpunit: No such file or directory"

**Problem**: Dependencies are not installed.

**Solution**: Install composer dependencies first.

```bash
# Inside container
composer install

# Or from host
make composer-install
```

### Tests Are Failing

1. **Clear cache:**
   ```bash
   rm -rf .phpunit.cache var/cache/test
   bin/console cache:clear --env=test
   ```

2. **Check translations directory:**
   ```bash
   mkdir -p translations
   chmod -R 777 translations
   ```

3. **Skip network tests if offline:**
   ```bash
   bin/phpunit --exclude-group network
   ```

## Examples

### Example 1: Quick Test Run (Inside Container)

```bash
root@ed2da4b435e3:/app# bin/phpunit --testsuite "Unit Tests" --colors=always
PHPUnit 11.0.0 by Sebastian Bergmann and contributors.

.................                                          17 / 17 (100%)

Time: 00:01.234, Memory: 18.00 MB

OK (17 tests, 42 assertions)
```

### Example 2: Run All Tests (From Host)

```bash
$ make test
docker-compose -f docker/docker-compose.yaml exec php-atic-gy bin/phpunit
PHPUnit 11.0.0 by Sebastian Bergmann and contributors.

..................................................................  66 / 82 ( 80%)
................                                                    82 / 82 (100%)

Time: 00:03.456, Memory: 28.00 MB

OK (82 tests, 145 assertions)
```

## Visual Reference

```
┌─────────────────────────────────────────┐
│  Your Computer (Host)                   │
│                                         │
│  Terminal Prompt: user@hostname $       │
│                                         │
│  Use: make test                         │
│       make test-unit                    │
│                                         │
│  ┌───────────────────────────────────┐ │
│  │  Docker Container                 │ │
│  │                                   │ │
│  │  Prompt: root@xxxxx:/app#         │ │
│  │                                   │ │
│  │  Use: bin/phpunit                 │ │
│  │       bin/phpunit --colors        │ │
│  │                                   │ │
│  └───────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

## TL;DR

- **Inside container** → `bin/phpunit`
- **Outside container** → `make test`
- **Can't remember?** → Run `bin/phpunit` (works in both places if PHP is available)
